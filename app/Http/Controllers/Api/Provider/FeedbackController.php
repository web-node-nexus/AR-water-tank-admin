<?php

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $feedback = Feedback::with(['booking', 'customer'])
            ->where('provider_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return response()->json([
            'data' => $feedback->getCollection()->map(fn ($f) => [
                'id' => $f->id,
                'rating' => $f->rating,
                'review' => $f->review,
                'customer_name' => $f->customer?->name,
                'booking_number' => $f->booking?->booking_number,
                'created_at' => $f->created_at->format('d M Y'),
            ]),
            'meta' => [
                'avg_rating' => (float) $request->user()->rating_avg,
                'total' => $feedback->total(),
            ],
        ]);
    }
}
