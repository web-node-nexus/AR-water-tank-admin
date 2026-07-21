<?php

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $leaves = LeaveRequest::where('provider_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return response()->json([
            'data' => $leaves->items(),
            'meta' => ['total' => $leaves->total()],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:500',
        ]);

        $leave = LeaveRequest::create([
            ...$validated,
            'provider_id' => $request->user()->id,
            'status' => 'pending',
        ]);

        $request->user()->update(['availability_status' => 'unavailable']);

        return response()->json([
            'message' => 'Leave request submitted.',
            'data' => $leave,
        ], 201);
    }

    public function toggleAvailability(Request $request): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:available,busy,unavailable',
        ]);

        $request->user()->update(['availability_status' => $request->status]);

        return response()->json([
            'message' => 'Availability updated.',
            'status' => $request->status,
        ]);
    }
}
