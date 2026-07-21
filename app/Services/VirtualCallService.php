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
     * Initiate masked call: provider calls customer via company virtual number.
     * Customer sees virtual number, not provider's personal number.
     */
    public function initiateCall(ServiceProvider $provider, Booking $booking): array
    {
        $virtualNumber = config('integrations.exotel.virtual_number');
        $customerPhone = $this->formatPhone($booking->customer_phone);
        $providerPhone = $this->formatPhone($provider->phone);

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

        $subdomain = config('integrations.exotel.subdomain', 'api');
        $url = "https://{$subdomain}.exotel.com/v1/Accounts/{$sid}/Calls/connect.json";

        $response = Http::withBasicAuth($apiKey, $apiToken)
            ->asForm()
            ->post($url, [
                'From' => $providerPhone,
                'To' => $customerPhone,
                'CallerId' => $virtualNumber,
                'CallType' => 'trans',
                'StatusCallback' => url('/api/provider/calls/callback'),
            ]);

        if ($response->successful()) {
            $data = $response->json();
            $callSid = $data['Call']['Sid'] ?? null;

            $callLog->update([
                'status' => 'connected',
                'provider_call_id' => $callSid,
                'meta' => $data,
            ]);

            return [
                'success' => true,
                'mode' => 'live',
                'message' => 'Connecting your call via company number',
                'virtual_number' => $virtualNumber,
                'call_log_id' => $callLog->id,
            ];
        }

        $callLog->update(['status' => 'failed', 'meta' => ['error' => $response->body()]]);

        return [
            'success' => false,
            'message' => 'Unable to initiate call. Please try again.',
        ];
    }

    protected function formatPhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);

        if (strlen($phone) === 10) {
            return '0'.$phone;
        }

        return $phone;
    }
}
