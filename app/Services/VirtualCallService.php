<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\CallLog;
use App\Models\ServiceProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VirtualCallService
{
    /**
     * Masked click-to-call via Exotel.
     *
     * Dial order:
     * 1) Customer is dialed first (their phone rings with virtual CallerId)
     * 2) After customer answers, provider is dialed and both are bridged
     */
    public function initiateCall(ServiceProvider $provider, Booking $booking): array
    {
        $virtualNumber = $this->formatCallerId((string) config('integrations.exotel.virtual_number'));
        $customerPhone = $this->formatPhone((string) $booking->customer_phone);
        $providerPhone = $this->formatPhone((string) $provider->phone);

        $callLog = CallLog::create([
            'provider_id' => $provider->id,
            'booking_id' => $booking->id,
            'customer_phone' => $booking->customer_phone,
            'virtual_number' => $virtualNumber,
            'provider_phone' => $provider->phone,
            'status' => 'initiated',
        ]);

        $sid = config('integrations.exotel.sid');
        $apiKey = config('integrations.exotel.api_key');
        $apiToken = config('integrations.exotel.api_token');

        if (! $sid || ! $apiKey || ! $apiToken || ! $virtualNumber) {
            $callLog->update([
                'status' => 'connected',
                'provider_call_id' => 'demo-'.uniqid(),
                'meta' => ['mode' => 'demo'],
            ]);

            return [
                'success' => true,
                'mode' => 'demo',
                'message' => 'Demo mode: configure EXOTEL_* in .env for live calls.',
                'virtual_number' => $virtualNumber,
                'call_log_id' => $callLog->id,
            ];
        }

        if (! $providerPhone || ! $customerPhone) {
            $callLog->update(['status' => 'failed', 'meta' => ['error' => 'Missing phone']]);

            return [
                'success' => false,
                'message' => 'Provider or customer phone number is missing.',
            ];
        }

        if ($providerPhone === $customerPhone) {
            $callLog->update(['status' => 'failed', 'meta' => ['error' => 'Same numbers']]);

            return [
                'success' => false,
                'message' => 'Provider and customer numbers are the same. Update customer phone in booking.',
            ];
        }

        // Customer first: customer phone rings; after answer, provider is bridged.
        $from = $customerPhone;
        $to = $providerPhone;

        $primary = config('integrations.exotel.subdomain', 'api');
        $hosts = array_values(array_unique([$primary, 'api', 'api.in']));

        $payload = [
            'From' => $from,
            'To' => $to,
            'CallerId' => $virtualNumber,
            'CallType' => 'trans',
            'TimeOut' => 45,
            'StatusCallback' => url('/api/provider/calls/callback'),
            'CustomField' => 'call_log_id:'.$callLog->id,
        ];

        $response = null;
        $usedHost = $primary;
        $lastBody = null;

        foreach ($hosts as $subdomain) {
            $usedHost = $subdomain;
            $url = "https://{$subdomain}.exotel.com/v1/Accounts/{$sid}/Calls/connect.json";

            Log::info('Exotel connect request', [
                'url' => $url,
                'from_customer' => $from,
                'to_provider' => $to,
                'caller_id' => $virtualNumber,
                'call_log_id' => $callLog->id,
            ]);

            $response = Http::withBasicAuth($apiKey, $apiToken)
                ->asForm()
                ->timeout(30)
                ->post($url, $payload);

            $lastBody = $response->body();

            if ($response->status() === 401) {
                Log::warning('Exotel auth failed on host, trying next', [
                    'host' => $subdomain,
                    'body' => $lastBody,
                ]);
                continue;
            }

            break;
        }

        if ($response && $response->successful()) {
            $data = $response->json();
            $callSid = $data['Call']['Sid'] ?? null;
            $responseTo = $data['Call']['To'] ?? null;

            $callLog->update([
                'status' => 'connected',
                'provider_call_id' => $callSid,
                'meta' => [
                    'host' => $usedHost,
                    'dial_order' => 'customer_first',
                    'request' => $payload,
                    'response' => $data,
                ],
            ]);

            if (empty($responseTo)) {
                return [
                    'success' => false,
                    'message' => 'Customer call was not queued. On Exotel free trial, verify customer number in Exotel dashboard first.',
                    'virtual_number' => $virtualNumber,
                    'call_log_id' => $callLog->id,
                ];
            }

            return [
                'success' => true,
                'mode' => 'live',
                'message' => 'Customer ki phone pe call ja rahi hai ('.$virtualNumber.' se). Customer uthaye ke baad aapki phone bajegi. Trial pe customer number Exotel me verified hona zaroori hai.',
                'virtual_number' => $virtualNumber,
                'from' => $from,
                'to' => $to,
                'call_log_id' => $callLog->id,
            ];
        }

        $body = $lastBody ?? '';
        Log::error('Exotel connect failed', [
            'status' => $response?->status(),
            'body' => $body,
            'from' => $from,
            'to' => $to,
            'host' => $usedHost,
        ]);

        $callLog->update([
            'status' => 'failed',
            'meta' => [
                'host' => $usedHost,
                'request' => $payload,
                'http_status' => $response?->status(),
                'error' => $body,
            ],
        ]);

        $message = 'Unable to initiate call. Please try again.';
        $json = $response?->json();
        if (is_array($json)) {
            $message = $json['RestException']['Message'] ?? $json['message'] ?? $message;
        }
        if ($response?->status() === 401) {
            $message = 'Exotel authentication failed. Check API key/token and EXOTEL_SUBDOMAIN.';
        }

        return [
            'success' => false,
            'message' => $message,
        ];
    }

    public function handleStatusCallback(array $payload): void
    {
        $callSid = $payload['CallSid'] ?? $payload['Sid'] ?? null;
        $status = $payload['Status'] ?? $payload['DialCallStatus'] ?? null;
        $custom = (string) ($payload['CustomField'] ?? '');
        $callLogId = null;

        if (preg_match('/call_log_id:(\d+)/', $custom, $m)) {
            $callLogId = (int) $m[1];
        }

        $callLog = $callLogId
            ? CallLog::find($callLogId)
            : ($callSid ? CallLog::where('provider_call_id', $callSid)->latest()->first() : null);

        if (! $callLog) {
            Log::info('Exotel callback without matching call log', $payload);

            return;
        }

        $meta = $callLog->meta ?? [];
        $meta['callbacks'] = array_values(array_merge($meta['callbacks'] ?? [], [$payload]));

        $update = ['meta' => $meta];
        if ($status) {
            $update['status'] = strtolower((string) $status);
        }
        if (isset($payload['DialCallDuration']) || isset($payload['Duration'])) {
            $update['duration_seconds'] = (int) ($payload['DialCallDuration'] ?? $payload['Duration']);
        }

        $callLog->update($update);
    }

    /**
     * Indian Exotel numbers: 0 + 10-digit mobile (e.g. 09876543210).
     */
    protected function formatPhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone) ?? '';

        if ($digits === '') {
            return '';
        }

        // +91 / 91XXXXXXXXXX
        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            return '0'.substr($digits, 2);
        }

        // Already 0XXXXXXXXXX
        if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            return $digits;
        }

        // 10-digit mobile
        if (strlen($digits) === 10) {
            return '0'.$digits;
        }

        return $digits;
    }

    /**
     * ExoPhone CallerId without dashes (e.g. 01143060441).
     */
    protected function formatCallerId(string $number): string
    {
        return preg_replace('/\D/', '', $number) ?? '';
    }
}
