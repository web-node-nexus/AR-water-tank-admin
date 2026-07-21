<?php

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use App\Models\ProviderDevice;
use App\Models\ServiceProvider;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct(protected OtpService $otpService) {}

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'fcm_token' => 'nullable|string',
            'device_name' => 'nullable|string',
            'platform' => 'nullable|string',
        ]);

        $email = strtolower(trim($request->email));
        $password = trim($request->password);

        $provider = ServiceProvider::where('email', $email)->where('is_active', true)->first();

        if (! $provider || ! $provider->password || ! Hash::check($password, $provider->password)) {
            return response()->json(['message' => 'Invalid email or password.'], 401);
        }

        return $this->issueToken($provider, $request);
    }

    public function sendOtp(Request $request): JsonResponse
    {
        $request->validate(['phone' => 'required|string|min:10|max:15']);

        $phone = $this->otpService->normalizePhone($request->phone);

        $provider = ServiceProvider::where('phone', $phone)->where('is_active', true)->first();

        if (! $provider) {
            return response()->json([
                'message' => 'No active service provider account found with this number.',
            ], 404);
        }

        $result = $this->otpService->send($phone);

        return response()->json([
            'message' => 'OTP sent successfully.',
            'data' => $result,
        ]);
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string|min:10|max:15',
            'otp' => 'required|string|size:6',
            'fcm_token' => 'nullable|string',
            'device_name' => 'nullable|string',
            'platform' => 'nullable|string',
        ]);

        $phone = $this->otpService->normalizePhone($request->phone);

        if (! $this->otpService->verify($phone, $request->otp)) {
            return response()->json(['message' => 'Invalid or expired OTP.'], 422);
        }

        $provider = ServiceProvider::where('phone', $phone)->where('is_active', true)->firstOrFail();

        return $this->issueToken($provider, $request);
    }

    protected function issueToken(ServiceProvider $provider, Request $request): JsonResponse
    {
        $provider->tokens()->delete();
        $token = $provider->createToken('provider-app')->plainTextToken;

        $provider->update(['last_login_at' => now()]);

        if ($request->fcm_token) {
            $provider->update(['fcm_token' => $request->fcm_token]);
            ProviderDevice::updateOrCreate(
                ['provider_id' => $provider->id, 'fcm_token' => $request->fcm_token],
                [
                    'device_name' => $request->device_name,
                    'platform' => $request->platform,
                    'last_used_at' => now(),
                ]
            );
        }

        return response()->json([
            'message' => 'Login successful.',
            'data' => [
                'token' => $token,
                'provider' => $this->formatProvider($provider->load('zone')),
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->formatProvider($request->user()->load('zone')),
        ]);
    }

    protected function formatProvider(ServiceProvider $provider): array
    {
        return [
            'id' => $provider->id,
            'name' => $provider->name,
            'phone' => $provider->phone,
            'email' => $provider->email,
            'photo' => $provider->photo ? url('storage/'.$provider->photo) : null,
            'service_area' => $provider->service_area,
            'availability_status' => $provider->availability_status,
            'rating_avg' => (float) $provider->rating_avg,
            'total_jobs' => $provider->total_jobs,
            'total_earnings' => (float) $provider->total_earnings,
            'zone' => $provider->zone?->name,
        ];
    }
}
