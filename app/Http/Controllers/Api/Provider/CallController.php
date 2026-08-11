<?php

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\VirtualCallService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CallController extends Controller
{
    public function __construct(protected VirtualCallService $callService) {}

    /**
     * Initiate virtual number call to customer.
     * Customer sees company virtual number, not provider's personal number.
     */
    public function callCustomer(Request $request, Booking $booking): JsonResponse
    {
        if ($booking->provider_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $result = $this->callService->initiateCall($request->user(), $booking);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    public function callback(Request $request): JsonResponse
    {
        $this->callService->handleStatusCallback($request->all());

        return response()->json(['status' => 'received']);
    }
}
