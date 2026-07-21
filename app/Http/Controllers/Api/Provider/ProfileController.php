<?php

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $provider = $request->user()->load('zone');

        return response()->json(['data' => [
            'id' => $provider->id,
            'name' => $provider->name,
            'phone' => $provider->phone,
            'email' => $provider->email,
            'photo' => $provider->photo ? url(Storage::url($provider->photo)) : null,
            'service_area' => $provider->service_area,
            'availability_status' => $provider->availability_status,
            'rating_avg' => (float) $provider->rating_avg,
            'total_jobs' => $provider->total_jobs,
            'total_earnings' => (float) $provider->total_earnings,
            'zone' => $provider->zone?->name,
        ]]);
    }

    public function update(Request $request): JsonResponse
    {
        $provider = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'nullable|email|max:255',
            'service_area' => 'nullable|string|max:255',
            'availability_status' => 'sometimes|in:available,busy,unavailable',
            'photo' => 'nullable|image|max:5120',
        ]);

        if ($request->hasFile('photo')) {
            if ($provider->photo) {
                Storage::disk('public')->delete($provider->photo);
            }
            $validated['photo'] = $request->file('photo')->store('providers', 'public');
        }

        $provider->update($validated);

        return response()->json([
            'message' => 'Profile updated.',
            'data' => ['photo' => $provider->photo ? url(Storage::url($provider->photo)) : null],
        ]);
    }

    public function updateFcmToken(Request $request): JsonResponse
    {
        $request->validate(['fcm_token' => 'required|string']);

        $request->user()->update(['fcm_token' => $request->fcm_token]);

        return response()->json(['message' => 'FCM token updated.']);
    }
}
