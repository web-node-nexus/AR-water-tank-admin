<?php

namespace App\Http\Controllers\Api\Provider;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payout;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EarningsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $provider = $request->user();
        $startOfMonth = Carbon::now()->startOfMonth();

        $monthlyEarnings = Booking::where('provider_id', $provider->id)
            ->where('status', BookingStatus::Completed)
            ->where('completed_at', '>=', $startOfMonth)
            ->sum('amount');

        $pendingPayout = Payout::where('provider_id', $provider->id)
            ->where('status', 'pending')
            ->sum('amount');

        $recentJobs = Booking::with('service')
            ->where('provider_id', $provider->id)
            ->where('status', BookingStatus::Completed)
            ->latest('completed_at')
            ->limit(10)
            ->get()
            ->map(fn ($b) => [
                'booking_number' => $b->booking_number,
                'service' => $b->service?->name,
                'amount' => (float) $b->amount,
                'completed_at' => $b->completed_at?->format('d M Y'),
            ]);

        $payouts = Payout::where('provider_id', $provider->id)
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn ($p) => [
                'amount' => (float) $p->amount,
                'period' => $p->period_start->format('d M').' - '.$p->period_end->format('d M Y'),
                'status' => $p->status,
                'paid_at' => $p->paid_at?->format('d M Y'),
            ]);

        return response()->json([
            'data' => [
                'total_earnings' => (float) $provider->total_earnings,
                'monthly_earnings' => (float) $monthlyEarnings,
                'pending_payout' => (float) $pendingPayout,
                'recent_jobs' => $recentJobs,
                'payout_history' => $payouts,
            ],
        ]);
    }
}
