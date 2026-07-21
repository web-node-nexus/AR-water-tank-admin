<?php

namespace App\Http\Controllers\Api\Provider;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $provider = $request->user();
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();

        $todayJobs = Booking::where('provider_id', $provider->id)
            ->whereDate('scheduled_date', $today)
            ->count();

        $pendingJobs = Booking::where('provider_id', $provider->id)
            ->whereIn('status', [BookingStatus::Assigned, BookingStatus::Pending])
            ->whereNull('provider_accepted_at')
            ->count();

        $inProgress = Booking::where('provider_id', $provider->id)
            ->where('status', BookingStatus::InProgress)
            ->count();

        $completedToday = Booking::where('provider_id', $provider->id)
            ->where('status', BookingStatus::Completed)
            ->whereDate('completed_at', $today)
            ->count();

        $monthlyEarnings = Booking::where('provider_id', $provider->id)
            ->where('status', BookingStatus::Completed)
            ->where('completed_at', '>=', $startOfMonth)
            ->sum('amount');

        $checkedIn = Attendance::where('provider_id', $provider->id)
            ->whereDate('date', $today)
            ->exists();

        $upcomingJobs = Booking::with(['service'])
            ->where('provider_id', $provider->id)
            ->whereDate('scheduled_date', '>=', $today)
            ->whereIn('status', [BookingStatus::Assigned, BookingStatus::InProgress, BookingStatus::Pending])
            ->orderBy('scheduled_date')
            ->orderBy('scheduled_time')
            ->limit(5)
            ->get()
            ->map(fn ($b) => $this->formatJobSummary($b));

        return response()->json([
            'data' => [
                'stats' => [
                    'today_jobs' => $todayJobs,
                    'pending_acceptance' => $pendingJobs,
                    'in_progress' => $inProgress,
                    'completed_today' => $completedToday,
                    'monthly_earnings' => (float) $monthlyEarnings,
                    'rating_avg' => (float) $provider->rating_avg,
                    'checked_in_today' => $checkedIn,
                ],
                'upcoming_jobs' => $upcomingJobs,
            ],
        ]);
    }

    protected function formatJobSummary(Booking $booking): array
    {
        return [
            'id' => $booking->id,
            'booking_number' => $booking->booking_number,
            'customer_name' => $booking->customer_name,
            'service_name' => $booking->service?->name,
            'scheduled_date' => $booking->scheduled_date->format('Y-m-d'),
            'scheduled_time' => $booking->scheduled_time ? substr($booking->scheduled_time, 0, 5) : null,
            'status' => $booking->status->value,
            'amount' => (float) $booking->amount,
        ];
    }
}
