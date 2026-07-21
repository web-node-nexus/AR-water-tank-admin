<?php

namespace App\Services;

use App\Models\OtpVerification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OtpService
{
    public function send(string $phone): array
    {
        $phone = $this->normalizePhone($phone);
        $otp = (string) random_int(100000, 999999);
        $expiry = config('integrations.otp.expiry_minutes', 10);

        OtpVerification::where('phone', $phone)->delete();

        OtpVerification::create([
            'phone' => $phone,
            'otp' => $otp,
            'expires_at' => now()->addMinutes($expiry),
        ]);

        $driver = config('integrations.otp.driver', 'log');

        if ($driver === 'msg91' && config('integrations.otp.msg91.auth_key')) {
            $this->sendViaMsg91($phone, $otp);
        } else {
            Log::info("OTP for {$phone}: {$otp}");
        }

        return [
            'phone' => $phone,
            'expires_in' => $expiry * 60,
            'debug_otp' => app()->environment('local') ? $otp : null,
        ];
    }

    public function verify(string $phone, string $otp): bool
    {
        $phone = $this->normalizePhone($phone);

        $record = OtpVerification::where('phone', $phone)
            ->where('is_verified', false)
            ->latest()
            ->first();

        if (! $record || $record->expires_at->isPast()) {
            return false;
        }

        if ($record->attempts >= 5) {
            return false;
        }

        $record->increment('attempts');

        if ($record->otp !== $otp) {
            return false;
        }

        $record->update(['is_verified' => true]);

        return true;
    }

    protected function sendViaMsg91(string $phone, string $otp): void
    {
        $config = config('integrations.otp.msg91');

        Http::withHeaders([
            'authkey' => $config['auth_key'],
            'Content-Type' => 'application/json',
        ])->post('https://control.msg91.com/api/v5/otp', [
            'template_id' => $config['template_id'],
            'mobile' => '91'.ltrim($phone, '0'),
            'otp' => $otp,
        ]);
    }

    public function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);

        if (strlen($phone) === 12 && str_starts_with($phone, '91')) {
            $phone = substr($phone, 2);
        }

        return $phone;
    }
}
