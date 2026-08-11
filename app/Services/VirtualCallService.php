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
     * Initiate masked call: provider is dialed first, then customer is bridged.
     * Both parties see the company virtual number (CallerId), not each other's number.
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
            Log::info('Virtual call simulated', [
                'provider' => $providerPhone,
                'customer' => $customerPhone,
                'virtual' => $virtualNumber,
            ]);

            $callLog->update([
                'status' => 'connected',
                'provider_call_id' => 'demo-'.uniqid(),
                'meta' => ['mode' => 'demo', 'message' => 'Configure EXOTEL_* in .env for live calls'],
            ]);

            return [
                'success' => true,
                'mode' => 'demo',
                'message' => 'Call initiated. Customer will receive call from company number '.($virtualNumber ?: 'XXXXXXXXXX'),
                'virtual_number' => $virtualNumber,
                'call_log_id' => $callLog->id,
            ];
        }

        if (! $providerPhone || ! $customerPhone) {
            $callLog->update(['status' => 'failed', 'meta' => ['error' => 'Missing provider or customer phone']]);

            return [
                'success' => false,
                'message' => 'Provider or customer phone number is missing.',
            ];
        }

        if ($providerPhone === $customerPhone) {
            $callLog->update(['status' => 'failed', 'meta' => ['error' => 'From and To are the same number']]);

            return [
                'success' => false,
                'message' => 'Provider and customer numbers are the same. Update customer phone in booking.',
            ];
        }

        // Prefer configured host; Indian accounts may need api.in, others api.
        $primary = config('integrations.exotel.subdomain', 'api');
        $hosts = array_values(array_unique([$primary, 'api', 'api.in']));

        $payload = [
            'From' => $providerPhone,
            'To' => $customerPhone,
            'CallerId' => $virtualNumber,
            'CallType' => 'trans',
            'TimeOut' => 45,
            'StatusCallback' => url('/api/provider/calls/callback'),
            'StatusCallbackContentType' => 'application/json',
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
                'from' => $providerPhone,
                'to' => $customerPhone,
                'caller_id' => $virtualNumber,
                'call_log_id' => $callLog->id,
            ]);

            $response = Http::withBasicAuth($apiKey, $apiToken)
                ->asForm()
                ->timeout(30)
                ->post($url, $payload);

            $lastBody = $response->body();

            // Wrong datacenter often returns 401 — try the other host.
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
                    'request' => $payload,
                    'response' => $data,
                ],
            ]);

            // If Exotel response has empty To, second leg will never dial.
            if (empty($responseTo)) {
                Log::warning('Exotel response missing To leg', ['call_sid' => $callSid, 'data' => $data]);

                return [
                    'success' => false,
                    'message' => 'Call started but customer leg was not queued. On Exotel trial, verify the customer number first.',
                    'virtual_number' => $virtualNumber,
                    'call_log_id' => $callLog->id,
                ];
            }

            return [
                'success' => true,
                'mode' => 'live',
                'message' => 'Your phone will ring first. After you answer, customer will be connected via '.$virtualNumber,
                'virtual_number' => $virtualNumber,
                'from' => $providerPhone,
                'to' => $customerPhone,
                'call_log_id' => $callLog->id,
            ];
        }

        $body = $lastBody ?? '';
        Log::error('Exotel connect failed', [
            'status' => $response?->status(),
            'body' => $body,
            'from' => $providerPhone,
            'to' => $customerPhone,
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
            $message = $json['RestException']['Message']
                ?? $json['message']
                ?? $message;
        }

        if ($response?->status() === 401) {
            $message = 'Exotel authentication failed. Check API key/token and EXOTEL_SUBDOMAIN (api or api.in).';
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
     * Exotel India expects E.164 for From/To: +91XXXXXXXXXX
     */
    protected function formatPhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone) ?? '';

        if ($phone === '') {
            return '';
        }

        // 9198XXXXXXXX -> +9198XXXXXXXX
        if (strlen($phone) === 12 && str_starts_with($phone, '91')) {
            return '+'.$phone;
        }

        // 098XXXXXXXX -> +9198XXXXXXXX
        if (strlen($phone) === 11 && str_starts_with($phone, '0')) {
            return '+91'.substr($phone, 1);
        }

        // 98XXXXXXXX (10-digit mobile)
        if (strlen($phone) === 10) {
            return '+91'.$phone;
        }

        if (str_starts_with($phone, '91') && strlen($phone) > 10) {
            return '+'.$phone;
        }

        return '+'.$phone;
    }

    /**
     * ExoPhone CallerId: digits only, no +, no dashes (e.g. 01143060441)
     */
    protected function formatCallerId(string $number): string
    {
        return preg_replace('/\D/', '', $number) ?? '';
    }
}
