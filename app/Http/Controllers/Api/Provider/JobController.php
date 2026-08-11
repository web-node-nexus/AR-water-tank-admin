<?php

namespace App\Http\Controllers\Api\Provider;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\JobPhoto;
use App\Services\WhatsAppService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class JobController extends Controller
{
    public function __construct(protected WhatsAppService $whatsApp) {}

    public function index(Request $request): JsonResponse
    {
        $query = Booking::with(['service'])
            ->where('provider_id', $request->user()->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('scheduled_date', $request->date);
        }

        if ($request->get('filter') === 'today') {
            $query->whereDate('scheduled_date', today());
        }

        $jobs = $query->latest('scheduled_date')->paginate(20);

        return response()->json([
            'data' => $jobs->getCollection()->map(fn ($b) => $this->formatJob($b)),
            'meta' => [
                'current_page' => $jobs->currentPage(),
                'last_page' => $jobs->lastPage(),
                'total' => $jobs->total(),
            ],
        ]);
    }

    public function show(Request $request, Booking $booking): JsonResponse
    {
        $this->authorizeJob($request, $booking);

        $booking->load(['service', 'pricingSlab', 'photos', 'feedback']);

        return response()->json(['data' => $this->formatJobDetail($booking)]);
    }

    public function accept(Request $request, Booking $booking): JsonResponse
    {
        $this->authorizeJob($request, $booking);

        if ($booking->status !== BookingStatus::Assigned) {
            return response()->json(['message' => 'Job cannot be accepted in current status.'], 422);
        }

        $booking->update([
            'provider_accepted_at' => now(),
            'provider_rejected_at' => null,
        ]);

        return response()->json([
            'message' => 'Job accepted successfully.',
            'data' => $this->formatJobDetail($booking->fresh()),
        ]);
    }

    public function reject(Request $request, Booking $booking): JsonResponse
    {
        $this->authorizeJob($request, $booking);

        $request->validate(['reason' => 'required|string|max:500']);

        $booking->update([
            'status' => BookingStatus::Pending,
            'provider_id' => null,
            'assigned_at' => null,
            'provider_accepted_at' => null,
            'provider_rejected_at' => now(),
            'rejection_reason' => $request->reason,
        ]);

        return response()->json(['message' => 'Job rejected. Admin has been notified.']);
    }

    public function start(Request $request, Booking $booking): JsonResponse
    {
        $this->authorizeJob($request, $booking);

        if (! in_array($booking->status, [BookingStatus::Assigned, BookingStatus::Pending])) {
            return response()->json(['message' => 'Job cannot be started.'], 422);
        }

        $booking->update([
            'status' => BookingStatus::InProgress,
            'started_at' => now(),
            'provider_accepted_at' => $booking->provider_accepted_at ?? now(),
        ]);

        return response()->json([
            'message' => 'Job started.',
            'data' => $this->formatJobDetail($booking->fresh()),
        ]);
    }

    public function complete(Request $request, Booking $booking): JsonResponse
    {
        $this->authorizeJob($request, $booking);

        if ($booking->status !== BookingStatus::InProgress) {
            return response()->json(['message' => 'Job must be in progress to complete.'], 422);
        }

        $hasBefore = $booking->photos()->where('type', 'before')->exists();
        $hasAfter = $booking->photos()->where('type', 'after')->exists();

        if (! $hasBefore || ! $hasAfter) {
            return response()->json([
                'message' => 'Please upload both before and after photos before completing.',
            ], 422);
        }

        $booking->update([
            'status' => BookingStatus::Completed,
            'completed_at' => now(),
            'payment_status' => 'paid',
        ]);

        $provider = $request->user();
        $provider->increment('total_jobs');
        $provider->increment('total_earnings', $booking->amount);

        $this->whatsApp->sendJobCompleted($booking);

        return response()->json([
            'message' => 'Job completed successfully!',
            'data' => $this->formatJobDetail($booking->fresh()),
        ]);
    }

    public function uploadPhoto(Request $request, Booking $booking): JsonResponse
    {
        $this->authorizeJob($request, $booking);

        $request->validate([
            'photo' => 'required|image|max:10240',
            'type' => 'required|in:before,after',
        ]);

        $path = $request->file('photo')->store('job-photos/'.$booking->id, 'public');

        $photo = JobPhoto::create([
            'booking_id' => $booking->id,
            'provider_id' => $request->user()->id,
            'type' => $request->type,
            'file_path' => $path,
            'uploaded_at' => now(),
        ]);

        // Send photo to customer via WhatsApp API
        $this->whatsApp->sendJobPhoto($photo);

        return response()->json([
            'message' => 'Photo uploaded and sent to customer via WhatsApp.',
            'data' => [
                'id' => $photo->id,
                'type' => $photo->type,
                'url' => url(Storage::url($path)),
                'uploaded_at' => $photo->uploaded_at->toIso8601String(),
            ],
        ]);
    }

    protected function authorizeJob(Request $request, Booking $booking): void
    {
        if ($booking->provider_id != $request->user()->id) {
            abort(403, 'This job is not assigned to you.');
        }
    }

    protected function formatJob(Booking $booking): array
    {
        return [
            'id' => $booking->id,
            'booking_number' => $booking->booking_number,
            'customer_name' => $booking->customer_name,
            'customer_address' => $booking->customer_address,
            'latitude' => $booking->latitude !== null ? (float) $booking->latitude : null,
            'longitude' => $booking->longitude !== null ? (float) $booking->longitude : null,
            'maps_url' => $booking->mapsUrl(),
            'service_name' => $booking->service?->name,
            'tank_type' => $booking->tank_type,
            'tank_size' => $booking->tank_size,
            'scheduled_date' => $booking->scheduled_date->format('Y-m-d'),
            'scheduled_time' => $booking->scheduled_time ? substr($booking->scheduled_time, 0, 5) : null,
            'status' => $booking->status->value,
            'amount' => (float) $booking->amount,
            'is_accepted' => (bool) $booking->provider_accepted_at,
        ];
    }

    protected function formatJobDetail(Booking $booking): array
    {
        return array_merge($this->formatJob($booking), [
            'pincode' => $booking->pincode,
            'special_notes' => $booking->special_notes,
            'started_at' => $booking->started_at?->toIso8601String(),
            'completed_at' => $booking->completed_at?->toIso8601String(),
            'photos' => $booking->photos->map(fn ($p) => [
                'id' => $p->id,
                'type' => $p->type,
                'url' => url(Storage::url($p->file_path)),
                'uploaded_at' => $p->uploaded_at->toIso8601String(),
            ]),
            'feedback' => $booking->feedback ? [
                'rating' => $booking->feedback->rating,
                'review' => $booking->feedback->review,
            ] : null,
        ]);
    }
}
