<?php

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\CallLog;
use App\Services\VirtualCallService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CallController extends Controller
{
    public function __construct(protected VirtualCallService $callService) {}

    /**
     * Initiate virtual number call to customer.
     * Customer sees company virtual number + configured business caller name.
     */
    public function callCustomer(Request $request, Booking $booking): JsonResponse
    {
        if ((int) $booking->provider_id !== (int) $request->user()->id) {
            return response()->json([
                'message' => 'This job is not assigned to you.',
                'success' => false,
            ], 403);
        }

        try {
            $result = $this->callService->initiateCall($request->user(), $booking);

            return response()->json($result, ! empty($result['success']) ? 200 : 422);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('callCustomer failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Could not start the call. Please try again.',
            ], 422);
        }
    }

    /**
     * Poll call status so app can keep "Connecting…" until provider phone rings.
     */
    public function status(Request $request, CallLog $callLog): JsonResponse
    {
        if ((int) $callLog->provider_id !== (int) $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $this->callService->getCallStatus($callLog),
        ]);
    }

    public function callback(Request $request): JsonResponse
    {
        $this->callService->handleStatusCallback($request->all());

        return response()->json(['status' => 'received']);
    }
}
