@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Welcome back, ' . auth()->user()->name)

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        @include('admin.partials.stat-card', [
            'label' => "Today's Bookings",
            'value' => $stats['bookings_today'],
            'color' => 'cyan',
            'icon' => '<svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>'
        ])
        @include('admin.partials.stat-card', [
            'label' => 'Monthly Revenue',
            'value' => '₹' . number_format($stats['revenue_month']),
            'color' => 'emerald',
            'icon' => '<svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/></svg>'
        ])
        @include('admin.partials.stat-card', [
            'label' => 'Pending Jobs',
            'value' => $stats['pending_jobs'],
            'color' => 'amber',
            'icon' => '<svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        ])
        @include('admin.partials.stat-card', [
            'label' => 'Completion Rate',
            'value' => $stats['completion_rate'] . '%',
            'color' => 'violet',
            'icon' => '<svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        ])
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        @include('admin.partials.stat-card', [
            'label' => 'Active Providers',
            'value' => $stats['active_providers'],
            'color' => 'indigo',
            'icon' => '<svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>'
        ])
        @include('admin.partials.stat-card', [
            'label' => 'In Progress',
            'value' => $stats['in_progress'],
            'color' => 'rose',
            'icon' => '<svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>'
        ])
        @include('admin.partials.stat-card', [
            'label' => 'Total Customers',
            'value' => $stats['total_customers'],
            'color' => 'cyan',
            'icon' => '<svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>'
        ])
        @include('admin.partials.stat-card', [
            'label' => 'Avg Rating',
            'value' => number_format($stats['avg_rating'], 1) . ' ★',
            'color' => 'amber',
            'icon' => '<svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>'
        ])
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                <h2 class="font-semibold text-slate-900">Recent Bookings</h2>
                <a href="{{ route('admin.bookings.create') }}" class="text-sm font-medium text-cyan-600 hover:text-cyan-700">+ New Booking</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500">
                        <tr>
                            <th class="text-left px-6 py-3 font-medium">Booking #</th>
                            <th class="text-left px-6 py-3 font-medium">Customer</th>
                            <th class="text-left px-6 py-3 font-medium">Service</th>
                            <th class="text-left px-6 py-3 font-medium">Status</th>
                            <th class="text-left px-6 py-3 font-medium">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentBookings as $booking)
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-6 py-3">
                                <a href="{{ route('admin.bookings.show', $booking) }}" class="font-medium text-cyan-600 hover:underline">{{ $booking->booking_number }}</a>
                            </td>
                            <td class="px-6 py-3 text-slate-700">{{ $booking->customer_name }}</td>
                            <td class="px-6 py-3 text-slate-600">{{ $booking->service?->name }}</td>
                            <td class="px-6 py-3">@include('admin.partials.status-badge', ['status' => $booking->status])</td>
                            <td class="px-6 py-3 font-medium">₹{{ number_format($booking->amount) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-6 py-8 text-center text-slate-500">No bookings yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
                <h2 class="font-semibold text-slate-900 mb-4">Quick Actions</h2>
                <div class="space-y-2">
                    <a href="{{ route('admin.bookings.create') }}" class="flex items-center gap-3 p-3 rounded-xl bg-cyan-50 text-cyan-700 hover:bg-cyan-100 transition text-sm font-medium">
                        <span class="w-8 h-8 rounded-lg bg-cyan-600 text-white flex items-center justify-center text-lg">+</span>
                        Create New Booking
                    </a>
                    <a href="{{ route('admin.providers.create') }}" class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 text-slate-700 hover:bg-slate-100 transition text-sm font-medium">
                        <span class="w-8 h-8 rounded-lg bg-indigo-600 text-white flex items-center justify-center">👤</span>
                        Add Service Provider
                    </a>
                    <a href="{{ route('admin.reports.index') }}" class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 text-slate-700 hover:bg-slate-100 transition text-sm font-medium">
                        <span class="w-8 h-8 rounded-lg bg-emerald-600 text-white flex items-center justify-center">📊</span>
                        View Reports
                    </a>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
                <h2 class="font-semibold text-slate-900 mb-4">Status Overview</h2>
                <div class="space-y-3">
                    @foreach(['pending', 'assigned', 'in_progress', 'completed', 'cancelled'] as $status)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-600 capitalize">{{ str_replace('_', ' ', $status) }}</span>
                            <span class="text-sm font-semibold text-slate-900">{{ $statusBreakdown[$status] ?? 0 }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            @if($stats['pending_leaves'] > 0)
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4">
                <p class="text-sm font-medium text-amber-800">{{ $stats['pending_leaves'] }} pending leave request(s)</p>
                <a href="{{ route('admin.leaves.index') }}" class="text-xs text-amber-600 hover:underline mt-1 inline-block">Review now →</a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
