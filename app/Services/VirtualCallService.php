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
     * 1) Provider is dialed first (must answer)
     * 2) Then customer/user is dialed and both are bridged
     * Customer sees company ExoPhone (+ configured business caller name via Truecaller/CNAM).
     */
    public function initiateCall(ServiceProvider $provider, Booking $booking): array
    {
        $virtualNumber = $this->formatCallerId((string) config('integrations.exotel.virtual_number'));
        $callerName = (string) config('integrations.exotel.caller_name', 'A.R WATER TANK CLEANER');
        $customerPhone = $this->formatPhone((string) $booking->customer_phone);
        $providerPhone = $this->formatPhone((string) $provider->phone);

        $callLog = CallLog::create([
            'provider_id' => $provider->id,
            'booking_id' => $booking->id,
            'customer_phone' => $booking->customer_phone,
            'virtual_number' => $virtualNumber,
            'provider_phone' => $provider->phone,
            'status' => 'initiated',
            'meta' => [
                'caller_name' => $callerName,
            ],
        ]);

        $sid = config('integrations.exotel.sid');
        $apiKey = config('integrations.exotel.api_key');
        $apiToken = config('integrations.exotel.api_token');

        if (! $sid || ! $apiKey || ! $apiToken || ! $virtualNumber) {
            $callLog->update([
                'status' => 'ringing',
                'provider_call_id' => 'demo-'.uniqid(),
                'meta' => array_merge($callLog->meta ?? [], ['mode' => 'demo']),
            ]);

            return [
                'success' => true,
                'mode' => 'demo',
                'message' => 'Demo mode: configure EXOTEL_* in .env for live calls.',
                'virtual_number' => $virtualNumber,
                'caller_name' => $callerName,
                'call_log_id' => $callLog->id,
                'status' => 'ringing',
            ];
        }

        if (! $providerPhone || ! $customerPhone) {
            $callLog->update(['status' => 'failed', 'meta' => array_merge($callLog->meta ?? [], ['error' => 'Missing phone'])]);

            return [
                'success' => false,
                'message' => 'Provider or customer phone number is missing.',
                'call_log_id' => $callLog->id,
                'status' => 'failed',
            ];
        }

        if ($providerPhone === $customerPhone) {
            $callLog->update(['status' => 'failed', 'meta' => array_merge($callLog->meta ?? [], ['error' => 'Same numbers'])]);

            return [
                'success' => false,
                'message' => 'Provider and customer numbers are the same. Update customer phone in booking.',
                'call_log_id' => $callLog->id,
                'status' => 'failed',
            ];
        }

        // Provider first, then customer/user.
        $from = $providerPhone;
        $to = $customerPhone;

        $primary = config('integrations.exotel.subdomain', 'api');
        $hosts = array_values(array_unique([$primary, 'api', 'api.in']));

        $payload = [
            'From' => $from,
            'To' => $to,
            'CallerId' => $virtualNumber,
            // Display name for customer CLI (honoured when Exotel/Truecaller CNAM is enabled).
            'CallerName' => $callerName,
            'CallType' => 'trans',
            'TimeOut' => 45,
            'StatusCallback' => url('/api/provider/calls/callback'),
            'StatusCallbackEvents[0]' => 'terminal',
            'StatusCallbackEvents[1]' => 'answered',
            'CustomField' => 'call_log_id:'.$callLog->id.'|caller:'.$callerName,
        ];

        $response = null;
        $usedHost = $primary;
        $lastBody = null;

        foreach ($hosts as $subdomain) {
            $usedHost = $subdomain;
            $url = "https://{$subdomain}.exotel.com/v1/Accounts/{$sid}/Calls/connect.json";

            Log::info('Exotel connect request', [
                'url' => $url,
                'from_provider' => $from,
                'to_customer' => $to,
                'caller_id' => $virtualNumber,
                'caller_name' => $callerName,
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
            $exotelStatus = strtolower((string) ($data['Call']['Status'] ?? 'queued'));

            $callLog->update([
                // Stay "queued" until StatusCallback / poll says ringing (provider phone ringing).
                'status' => $this->normalizeStatus($exotelStatus) === 'ringing' ? 'ringing' : 'queued',
                'provider_call_id' => $callSid,
                'meta' => [
                    'caller_name' => $callerName,
                    'host' => $usedHost,
                    'dial_order' => 'provider_first',
                    'request' => $payload,
                    'response' => $data,
                ],
            ]);

            if (empty($responseTo)) {
                return [
                    'success' => false,
                    'message' => 'Customer call was not queued. On Exotel free trial, verify customer number in Exotel dashboard first.',
                    'virtual_number' => $virtualNumber,
                    'caller_name' => $callerName,
                    'call_log_id' => $callLog->id,
                    'status' => $callLog->status,
                ];
            }

            return [
                'success' => true,
                'mode' => 'live',
                'message' => 'Connecting… Your phone will ring first.',
                'virtual_number' => $virtualNumber,
                'caller_name' => $callerName,
                'from' => $from,
                'to' => $to,
                'call_log_id' => $callLog->id,
                'status' => $callLog->status,
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
                'caller_name' => $callerName,
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
            'call_log_id' => $callLog->id,
            'status' => 'failed',
        ];
    }

    /**
     * Pollable status for provider app "Connecting…" loader.
     */
    public function getCallStatus(CallLog $callLog): array
    {
        $this->refreshFromExotel($callLog);
        $callLog->refresh();

        $status = strtolower((string) $callLog->status);
        $isRinging = in_array($status, ['ringing', 'in-progress', 'answered', 'connected', 'completed'], true);
        $isFailed = in_array($status, ['failed', 'busy', 'no-answer', 'canceled', 'cancelled'], true);
        $isTerminal = $isFailed || in_array($status, ['completed'], true);

        return [
            'call_log_id' => $callLog->id,
            'status' => $status,
            'is_ringing' => $isRinging,
            'is_failed' => $isFailed,
            'is_terminal' => $isTerminal,
            'caller_name' => $callLog->meta['caller_name'] ?? config('integrations.exotel.caller_name'),
            'virtual_number' => $callLog->virtual_number,
        ];
    }

    public function handleStatusCallback(array $payload): void
    {
        $callSid = $payload['CallSid'] ?? $payload['Sid'] ?? null;
        $status = $payload['Status'] ?? $payload['DialCallStatus'] ?? $payload['EventType'] ?? null;
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
            $update['status'] = $this->normalizeStatus((string) $status);
        }
        if (isset($payload['DialCallDuration']) || isset($payload['Duration'])) {
            $update['duration_seconds'] = (int) ($payload['DialCallDuration'] ?? $payload['Duration']);
        }

        $callLog->update($update);
    }

    protected function refreshFromExotel(CallLog $callLog): void
    {
        $callSid = $callLog->provider_call_id;
        if (! $callSid || str_starts_with((string) $callSid, 'demo-')) {
            return;
        }

        // Only poll Exotel while still waiting for provider ring.
        if (! in_array(strtolower((string) $callLog->status), ['initiated', 'queued'], true)) {
            return;
        }

        $sid = config('integrations.exotel.sid');
        $apiKey = config('integrations.exotel.api_key');
        $apiToken = config('integrations.exotel.api_token');
        if (! $sid || ! $apiKey || ! $apiToken) {
            return;
        }

        $primary = config('integrations.exotel.subdomain', 'api');
        $hosts = array_values(array_unique([$primary, 'api', 'api.in']));

        foreach ($hosts as $subdomain) {
            $url = "https://{$subdomain}.exotel.com/v1/Accounts/{$sid}/Calls/{$callSid}.json";
            $response = Http::withBasicAuth($apiKey, $apiToken)->timeout(10)->get($url);

            if ($response->status() === 401) {
                continue;
            }

            if (! $response->successful()) {
                return;
            }

            $data = $response->json();
            $exotelStatus = $data['Call']['Status'] ?? null;
            if (! $exotelStatus) {
                return;
            }

            $normalized = $this->normalizeStatus((string) $exotelStatus);
            $meta = $callLog->meta ?? [];
            $meta['polls'] = array_values(array_merge($meta['polls'] ?? [], [[
                'at' => now()->toIso8601String(),
                'status' => $normalized,
            ]]));

            $callLog->update([
                'status' => $normalized,
                'meta' => $meta,
            ]);

            return;
        }
    }

    protected function normalizeStatus(string $status): string
    {
        $status = strtolower(trim($status));

        return match ($status) {
            'in-progress', 'in_progress', 'answered' => 'in-progress',
            'no-answer', 'no_answer' => 'no-answer',
            'cancelled' => 'canceled',
            'terminal' => 'completed',
            default => $status,
        };
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
