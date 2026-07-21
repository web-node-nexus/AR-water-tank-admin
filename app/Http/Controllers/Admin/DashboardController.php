<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Feedback;
use App\Models\LeaveRequest;
use App\Models\ServiceProvider;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();

        $stats = [
            'bookings_today' => Booking::whereDate('scheduled_date', $today)->count(),
            'bookings_week' => Booking::where('scheduled_date', '>=', $startOfWeek)->count(),
            'bookings_month' => Booking::where('scheduled_date', '>=', $startOfMonth)->count(),
            'revenue_today' => Booking::whereDate('completed_at', $today)
                ->where('status', BookingStatus::Completed)->sum('amount'),
            'revenue_month' => Booking::where('completed_at', '>=', $startOfMonth)
                ->where('status', BookingStatus::Completed)->sum('amount'),
            'active_providers' => ServiceProvider::where('is_active', true)
                ->where('availability_status', 'available')->count(),
            'pending_jobs' => Booking::whereIn('status', [
                BookingStatus::Pending, BookingStatus::Assigned,
            ])->count(),
            'in_progress' => Booking::where('status', BookingStatus::InProgress)->count(),
            'completed_month' => Booking::where('status', BookingStatus::Completed)
                ->where('completed_at', '>=', $startOfMonth)->count(),
            'total_customers' => Customer::count(),
            'pending_leaves' => LeaveRequest::where('status', 'pending')->count(),
            'avg_rating' => Feedback::avg('rating') ?? 0,
        ];

        $totalMonth = Booking::where('scheduled_date', '>=', $startOfMonth)->count();
        $stats['completion_rate'] = $totalMonth > 0
            ? round(($stats['completed_month'] / $totalMonth) * 100, 1)
            : 0;

        $recentBookings = Booking::with(['service', 'provider'])
            ->latest()
            ->limit(8)
            ->get();

        $monthlyRevenue = Booking::select(
            DB::raw('DATE(completed_at) as date'),
            DB::raw('SUM(amount) as total')
        )
            ->where('status', BookingStatus::Completed)
            ->where('completed_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $statusBreakdown = Booking::select('status', DB::raw('count(*) as count'))
            ->where('scheduled_date', '>=', $startOfMonth)
            ->groupBy('status')
            ->pluck('count', 'status');

        return view('admin.dashboard', compact('stats', 'recentBookings', 'monthlyRevenue', 'statusBreakdown'));
    }
}
