<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\CallLog;
use App\Models\ServiceProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

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

            try {
                $response = Http::withBasicAuth($apiKey, $apiToken)
                    ->asForm()
                    ->timeout(20)
                    ->connectTimeout(10)
                    ->post($url, $payload);

                $lastBody = $response->body();
            } catch (Throwable $e) {
                Log::error('Exotel connect exception', [
                    'host' => $subdomain,
                    'error' => $e->getMessage(),
                    'call_log_id' => $callLog->id,
                ]);
                $lastBody = $e->getMessage();
                $response = null;
                continue;
            }

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

            try {
                $callLog->update([
                    // Stay "queued" until StatusCallback / poll says ringing (provider phone ringing).
                    'status' => $this->normalizeStatus($exotelStatus) === 'ringing' ? 'ringing' : 'queued',
                    'provider_call_id' => $callSid,
                    'meta' => [
                        'caller_name' => $callerName,
                        'host' => $usedHost,
                        'dial_order' => 'provider_first',
                        'request' => [
                            'From' => $payload['From'],
                            'To' => $payload['To'],
                            'CallerId' => $payload['CallerId'],
                            'CallerName' => $payload['CallerName'],
                        ],
                        'response_sid' => $callSid,
                        'response_status' => $exotelStatus,
                    ],
                ]);
            } catch (Throwable $e) {
                Log::error('Call log update failed after Exotel success', [
                    'call_log_id' => $callLog->id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Call was accepted by Exotel — treat as success even if secondary fields are empty.
            return [
                'success' => true,
                'mode' => 'live',
                'message' => empty($responseTo)
                    ? 'Call started. If customer does not get connected on trial accounts, verify their number in Exotel.'
                    : 'Connecting… Your phone will ring first.',
                'virtual_number' => $virtualNumber,
                'caller_name' => $callerName,
                'from' => $from,
                'to' => $to,
                'call_log_id' => $callLog->id,
                'status' => $callLog->fresh()?->status ?? 'queued',
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

    /**
     * Incoming ExoPhone call (customer calling back the virtual number).
     * Returns ExoML so Exotel Passthru / Voice URL can Dial the mapped provider.
     */
    public function handleIncomingPassthru(array $payload): string
    {
        $route = $this->prepareInboundRoute($payload);

        if (! $route) {
            $fromLast10 = $this->lastTen((string) ($payload['From'] ?? $payload['CallFrom'] ?? $payload['from'] ?? ''));

            if ($fromLast10 === '') {
                return $this->exomlHangup('We could not identify your number. Please try again.');
            }

            return $this->exomlHangup('Sorry, we could not connect your call right now. Please try again later.');
        }

        return $this->exomlDial($route['dialNumber'], $route['callerId']);
    }

    /**
     * Connect applet (App Bazaar) Application URL — JSON destination numbers.
     *
     * @return array<string, mixed>
     */
    public function handleIncomingConnect(array $payload): array
    {
        $route = $this->prepareInboundRoute($payload);

        if (! $route) {
            return [
                'fetch_after_attempt' => false,
                'destination' => ['numbers' => []],
            ];
        }

        $outgoing = $this->formatE164($route['callerId']);

        $response = [
            'fetch_after_attempt' => false,
            'destination' => [
                'numbers' => [$this->formatE164($route['dialNumber'])],
            ],
            'record' => false,
            'max_ringing_duration' => 45,
            'max_conversation_duration' => 3600,
            'music_on_hold' => ['type' => 'operator_tone'],
        ];

        if ($outgoing !== '') {
            $response['outgoing_phone_number'] = $outgoing;
        }

        return $response;
    }

    /**
     * @return array{dialNumber: string, callerId: string, destination: array{number: string, provider_id: int, booking_id: ?int, customer_phone: string, provider_phone: string, role: string}}|null
     */
    protected function prepareInboundRoute(array $payload): ?array
    {
        $from = (string) ($payload['From'] ?? $payload['CallFrom'] ?? $payload['from'] ?? '');
        $to = (string) ($payload['To'] ?? $payload['CallTo'] ?? $payload['to'] ?? '');
        $callSid = $payload['CallSid'] ?? $payload['Sid'] ?? null;
        $fromLast10 = $this->lastTen($from);
        $virtualNumber = $this->formatCallerId(
            $to !== '' ? $to : (string) config('integrations.exotel.virtual_number')
        );

        Log::info('Exotel incoming passthru', [
            'from' => $from,
            'to' => $to,
            'call_sid' => $callSid,
        ]);

        if ($fromLast10 === '') {
            return null;
        }

        $destination = $this->resolveInboundDestination($fromLast10);

        if (! $destination) {
            Log::warning('Exotel incoming: no mapping found', ['from' => $fromLast10]);

            return null;
        }

        if ($this->lastTen($destination['number']) === $fromLast10) {
            Log::warning('Exotel incoming: destination matches caller', ['from' => $fromLast10]);

            return null;
        }

        $dialNumber = $this->formatPhone($destination['number']);
        $callerId = $virtualNumber ?: $this->formatCallerId((string) config('integrations.exotel.virtual_number'));

        try {
            CallLog::create([
                'provider_id' => $destination['provider_id'],
                'booking_id' => $destination['booking_id'],
                'customer_phone' => $destination['customer_phone'],
                'virtual_number' => $callerId,
                'provider_phone' => $destination['provider_phone'],
                'provider_call_id' => is_string($callSid) ? $callSid : null,
                'status' => 'initiated',
                'meta' => [
                    'direction' => 'inbound_callback',
                    'from' => $from,
                    'to' => $to,
                    'dial' => $dialNumber,
                    'role' => $destination['role'],
                ],
            ]);
        } catch (Throwable $e) {
            Log::error('Exotel inbound call log failed', ['error' => $e->getMessage()]);
        }

        Log::info('Exotel incoming routed', [
            'from' => $fromLast10,
            'dial' => $dialNumber,
            'role' => $destination['role'],
        ]);

        return [
            'dialNumber' => $dialNumber,
            'callerId' => $callerId,
            'destination' => $destination,
        ];
    }

    /**
     * @return array{number: string, provider_id: int, booking_id: ?int, customer_phone: string, provider_phone: string, role: string}|null
     */
    protected function resolveInboundDestination(string $fromLast10): ?array
    {
        $asCustomer = $this->latestCallMatching('customer_phone', $fromLast10);
        if ($asCustomer?->provider_phone) {
            return [
                'number' => $asCustomer->provider_phone,
                'provider_id' => (int) $asCustomer->provider_id,
                'booking_id' => $asCustomer->booking_id ? (int) $asCustomer->booking_id : null,
                'customer_phone' => $asCustomer->customer_phone,
                'provider_phone' => $asCustomer->provider_phone,
                'role' => 'customer_to_provider',
            ];
        }

        $asProvider = $this->latestCallMatching('provider_phone', $fromLast10);
        if ($asProvider?->customer_phone) {
            return [
                'number' => $asProvider->customer_phone,
                'provider_id' => (int) $asProvider->provider_id,
                'booking_id' => $asProvider->booking_id ? (int) $asProvider->booking_id : null,
                'customer_phone' => $asProvider->customer_phone,
                'provider_phone' => $asProvider->provider_phone,
                'role' => 'provider_to_customer',
            ];
        }

        $booking = Booking::with('provider')
            ->whereNotNull('provider_id')
            ->where(function ($q) use ($fromLast10) {
                $this->applyPhoneVariants($q, 'customer_phone', $fromLast10);
            })
            ->latest('id')
            ->first();

        if ($booking?->provider?->phone) {
            return [
                'number' => $booking->provider->phone,
                'provider_id' => (int) $booking->provider_id,
                'booking_id' => (int) $booking->id,
                'customer_phone' => $booking->customer_phone,
                'provider_phone' => $booking->provider->phone,
                'role' => 'booking_fallback',
            ];
        }

        return null;
    }

    protected function latestCallMatching(string $column, string $last10): ?CallLog
    {
        return CallLog::query()
            ->where(function ($q) use ($column, $last10) {
                $this->applyPhoneVariants($q, $column, $last10);
            })
            ->where(function ($q) {
                $q->whereNull('meta')
                    ->orWhere('meta', 'not like', '%inbound_callback%');
            })
            ->where('created_at', '>=', now()->subDays(30))
            ->latest('id')
            ->first();
    }

    protected function applyPhoneVariants($query, string $column, string $last10): void
    {
        $variants = array_unique([
            $last10,
            '0'.$last10,
            '91'.$last10,
            '+91'.$last10,
        ]);

        $query->where(function ($q) use ($column, $variants, $last10) {
            foreach ($variants as $variant) {
                $q->orWhere($column, $variant);
            }
            $q->orWhere($column, 'like', '%'.$last10);
        });
    }

    protected function lastTen(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone) ?? '';

        return strlen($digits) >= 10 ? substr($digits, -10) : $digits;
    }

    protected function exomlDial(string $number, string $callerId): string
    {
        $numberXml = htmlspecialchars($number, ENT_XML1);
        $callerXml = htmlspecialchars($callerId, ENT_XML1);
        $callerAttr = $callerId !== '' ? ' callerId="'.$callerXml.'"' : '';

        return '<?xml version="1.0" encoding="UTF-8"?>'
            .'<Response>'
            .'<Dial'.$callerAttr.' timeout="45">'
            .'<Number>'.$numberXml.'</Number>'
            .'</Dial>'
            .'</Response>';
    }

    protected function exomlHangup(string $message): string
    {
        $say = htmlspecialchars($message, ENT_XML1);

        return '<?xml version="1.0" encoding="UTF-8"?>'
            .'<Response>'
            .'<Say>'.$say.'</Say>'
            .'<Hangup/>'
            .'</Response>';
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
     * E.164 for Connect applet destination numbers (e.g. +919876543210).
     */
    protected function formatE164(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone) ?? '';

        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, '91') && strlen($digits) >= 12) {
            return '+'.$digits;
        }

        if (str_starts_with($digits, '0')) {
            return '+91'.substr($digits, 1);
        }

        if (strlen($digits) === 10) {
            return '+91'.$digits;
        }

        return '+'.$digits;
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
