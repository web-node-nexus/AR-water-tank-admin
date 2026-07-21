<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\JobPhoto;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class WhatsAppService
{
    /**
     * Send job photo to customer via WhatsApp Business API.
     * Triggered when provider uploads before/after cleaning photos.
     */
    public function sendJobPhoto(JobPhoto $photo): bool
    {
        $booking = $photo->booking;
        $customerPhone = $this->formatPhone($booking->customer_phone);
        $imageUrl = url(Storage::url($photo->file_path));

        $typeLabel = $photo->type === 'before' ? 'Before Cleaning' : 'After Cleaning';
        $message = "Hello {$booking->customer_name}! 👋\n\n";
        $message .= "Your water tank cleaning update from *AR Water Tank Cleaners*:\n\n";
        $message .= "📋 Booking: {$booking->booking_number}\n";
        $message .= "📸 Photo Type: *{$typeLabel}*\n";
        $message .= "🔧 Service: {$booking->service?->name}\n";
        $message .= "📅 Date: ".now()->format('d M Y, h:i A')."\n\n";
        $message .= "Thank you for choosing AR Water Tank Cleaners! 🙏";

        return $this->sendImageMessage($customerPhone, $imageUrl, $message, $photo->type);
    }

    public function sendJobCompleted(Booking $booking): bool
    {
        $customerPhone = $this->formatPhone($booking->customer_phone);

        $message = "Hello {$booking->customer_name}! ✅\n\n";
        $message .= "Your water tank cleaning service has been *completed* successfully.\n\n";
        $message .= "📋 Booking: {$booking->booking_number}\n";
        $message .= "🔧 Service: {$booking->service?->name}\n";
        $message .= "💰 Amount: ₹".number_format($booking->amount)."\n\n";
        $message .= "We hope you're satisfied with our service. Please share your feedback!\n\n";
        $message .= "— AR Water Tank Cleaners";

        return $this->sendTextMessage($customerPhone, $message);
    }

    protected function sendImageMessage(string $phone, string $imageUrl, string $caption, string $type): bool
    {
        $driver = config('integrations.whatsapp.driver', 'log');

        if ($driver === 'log' || ! config('integrations.whatsapp.api_key')) {
            Log::info('WhatsApp image message (demo)', [
                'to' => $phone,
                'image' => $imageUrl,
                'caption' => $caption,
                'type' => $type,
            ]);

            return true;
        }

        return match ($driver) {
            'gupshup' => $this->sendViaGupshup($phone, $imageUrl, $caption),
            'interakt' => $this->sendViaInterakt($phone, $imageUrl, $caption),
            default => $this->sendViaGenericApi($phone, $imageUrl, $caption),
        };
    }

    protected function sendTextMessage(string $phone, string $message): bool
    {
        $driver = config('integrations.whatsapp.driver', 'log');

        if ($driver === 'log' || ! config('integrations.whatsapp.api_key')) {
            Log::info('WhatsApp text message (demo)', ['to' => $phone, 'message' => $message]);

            return true;
        }

        $apiUrl = config('integrations.whatsapp.api_url');
        $apiKey = config('integrations.whatsapp.api_key');
        $source = config('integrations.whatsapp.source_number');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$apiKey,
            'Content-Type' => 'application/json',
        ])->post($apiUrl, [
            'messaging_product' => 'whatsapp',
            'to' => $phone,
            'type' => 'text',
            'text' => ['body' => $message],
            'from' => $source,
        ]);

        return $response->successful();
    }

    protected function sendViaGupshup(string $phone, string $imageUrl, string $caption): bool
    {
        $response = Http::asForm()->post('https://api.gupshup.io/wa/api/v1/msg', [
            'channel' => 'whatsapp',
            'source' => config('integrations.whatsapp.source_number'),
            'destination' => $phone,
            'message' => json_encode([
                'type' => 'image',
                'originalUrl' => $imageUrl,
                'previewUrl' => $imageUrl,
                'caption' => $caption,
            ]),
            'src.name' => 'ARWaterTank',
        ], [
            'apikey' => config('integrations.whatsapp.api_key'),
        ]);

        return $response->successful();
    }

    protected function sendViaInterakt(string $phone, string $imageUrl, string $caption): bool
    {
        $response = Http::withHeaders([
            'Authorization' => 'Basic '.config('integrations.whatsapp.api_key'),
            'Content-Type' => 'application/json',
        ])->post('https://api.interakt.ai/v1/public/message/', [
            'countryCode' => '+91',
            'phoneNumber' => ltrim($phone, '91'),
            'type' => 'Image',
            'data' => [
                'mediaUrl' => $imageUrl,
                'caption' => $caption,
            ],
        ]);

        return $response->successful();
    }

    protected function sendViaGenericApi(string $phone, string $imageUrl, string $caption): bool
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.config('integrations.whatsapp.api_key'),
        ])->post(config('integrations.whatsapp.api_url'), [
            'to' => $phone,
            'type' => 'image',
            'image' => ['link' => $imageUrl, 'caption' => $caption],
        ]);

        return $response->successful();
    }

    protected function formatPhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);

        if (strlen($phone) === 10) {
            return '91'.$phone;
        }

        return $phone;
    }
}
