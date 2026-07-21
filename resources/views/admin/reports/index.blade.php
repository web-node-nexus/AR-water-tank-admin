@extends('layouts.admin')

@section('title', 'Reports')
@section('page-title', 'Reports & Analytics')
@section('page-subtitle', 'Revenue and performance insights')

@section('content')
<div class="space-y-6">
    <form method="GET" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-4 flex flex-wrap items-end gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Start Date</label>
            <input type="date" name="start_date" value="{{ $startDate }}" class="rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-cyan-500 outline-none">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">End Date</label>
            <input type="date" name="end_date" value="{{ $endDate }}" class="rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-cyan-500 outline-none">
        </div>
        <button type="submit" class="px-4 py-2 bg-slate-900 text-white text-sm font-medium rounded-xl hover:bg-slate-800">Apply</button>
    </form>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        @include('admin.partials.stat-card', [
            'label' => 'Revenue',
            'value' => '₹' . number_format($revenue),
            'color' => 'emerald',
            'icon' => '<svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/></svg>'
        ])
        @include('admin.partials.stat-card', [
            'label' => 'Total Jobs',
            'value' => $jobCount,
            'color' => 'cyan',
            'icon' => '<svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>'
        ])
        @include('admin.partials.stat-card', [
            'label' => 'Completed',
            'value' => $completedCount,
            'color' => 'violet',
            'icon' => '<svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        ])
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h2 class="font-semibold text-slate-900">Provider Earnings</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500">
                        <tr>
                            <th class="text-left px-6 py-3 font-medium">Provider</th>
                            <th class="text-left px-6 py-3 font-medium">Jobs</th>
                            <th class="text-left px-6 py-3 font-medium">Earnings</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($providerEarnings as $row)
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-6 py-3">
                                @if($row->provider)
                                <a href="{{ route('admin.providers.show', $row->provider) }}" class="font-medium text-cyan-600 hover:underline">{{ $row->provider->name }}</a>
                                @else
                                <span class="text-slate-400">Unknown</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 font-medium">{{ $row->jobs }}</td>
                            <td class="px-6 py-3 font-medium text-emerald-600">₹{{ number_format($row->earnings) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="px-6 py-8 text-center text-slate-500">No earnings data for this period</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h2 class="font-semibold text-slate-900">Service Breakdown</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500">
                        <tr>
                            <th class="text-left px-6 py-3 font-medium">Service</th>
                            <th class="text-left px-6 py-3 font-medium">Bookings</th>
                            <th class="text-left px-6 py-3 font-medium">Revenue</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($serviceBreakdown as $row)
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-6 py-3 font-medium text-slate-900">{{ $row->service?->name ?? 'Unknown' }}</td>
                            <td class="px-6 py-3">{{ $row->count }}</td>
                            <td class="px-6 py-3 font-medium text-emerald-600">₹{{ number_format($row->revenue) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="px-6 py-8 text-center text-slate-500">No service data for this period</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
