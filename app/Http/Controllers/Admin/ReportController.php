<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ServiceProvider;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));

        $revenue = Booking::where('status', BookingStatus::Completed)
            ->whereBetween('completed_at', [$startDate, $endDate.' 23:59:59'])
            ->sum('amount');

        $jobCount = Booking::whereBetween('scheduled_date', [$startDate, $endDate])
            ->count();

        $completedCount = Booking::where('status', BookingStatus::Completed)
            ->whereBetween('completed_at', [$startDate, $endDate.' 23:59:59'])
            ->count();

        $providerEarnings = Booking::select('provider_id', DB::raw('COUNT(*) as jobs'), DB::raw('SUM(amount) as earnings'))
            ->where('status', BookingStatus::Completed)
            ->whereBetween('completed_at', [$startDate, $endDate.' 23:59:59'])
            ->whereNotNull('provider_id')
            ->groupBy('provider_id')
            ->get()
            ->map(function ($row) {
                $row->provider = ServiceProvider::find($row->provider_id);

                return $row;
            });

        $serviceBreakdown = Booking::select('service_id', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as revenue'))
            ->whereBetween('scheduled_date', [$startDate, $endDate])
            ->groupBy('service_id')
            ->with('service')
            ->get();

        return view('admin.reports.index', compact(
            'revenue', 'jobCount', 'completedCount',
            'providerEarnings', 'serviceBreakdown', 'startDate', 'endDate'
        ));
    }
}
