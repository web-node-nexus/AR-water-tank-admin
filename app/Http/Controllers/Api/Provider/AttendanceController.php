<?php

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function checkIn(Request $request): JsonResponse
    {
        $provider = $request->user();
        $today = today();

        if (Attendance::where('provider_id', $provider->id)->whereDate('date', $today)->exists()) {
            return response()->json(['message' => 'Already checked in today.'], 422);
        }

        $validated = $request->validate([
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'address' => 'nullable|string|max:500',
        ]);

        $attendance = Attendance::create([
            'provider_id' => $provider->id,
            'date' => $today,
            'check_in_at' => now(),
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'address' => $validated['address'] ?? null,
        ]);

        return response()->json([
            'message' => 'Checked in successfully.',
            'data' => $attendance,
        ]);
    }

    public function checkOut(Request $request): JsonResponse
    {
        $attendance = Attendance::where('provider_id', $request->user()->id)
            ->whereDate('date', today())
            ->whereNull('check_out_at')
            ->first();

        if (! $attendance) {
            return response()->json(['message' => 'No active check-in found.'], 422);
        }

        $attendance->update(['check_out_at' => now()]);

        return response()->json(['message' => 'Checked out successfully.']);
    }

    public function today(Request $request): JsonResponse
    {
        $attendance = Attendance::where('provider_id', $request->user()->id)
            ->whereDate('date', today())
            ->first();

        return response()->json(['data' => $attendance]);
    }

    public function history(Request $request): JsonResponse
    {
        $history = Attendance::where('provider_id', $request->user()->id)
            ->latest('date')
            ->limit(30)
            ->get();

        return response()->json(['data' => $history]);
    }
}
