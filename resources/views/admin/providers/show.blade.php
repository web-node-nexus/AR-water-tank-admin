@extends('layouts.admin')

@section('title', $provider->name)
@section('page-title', 'Provider Details')
@section('page-subtitle', $provider->name)

@section('content')
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <div class="xl:col-span-2 space-y-6">
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
            <div class="flex items-start justify-between mb-6">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">{{ $provider->name }}</h2>
                    <p class="text-sm text-slate-500">{{ $provider->phone }} @if($provider->email) · {{ $provider->email }} @endif</p>
                </div>
                <div class="flex items-center gap-2">
                    @include('admin.partials.status-badge', ['status' => $provider->availability_status])
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $provider->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                        {{ $provider->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div><span class="text-slate-500">Zone</span><p class="font-medium">{{ $provider->zone?->name ?? '—' }}</p></div>
                <div><span class="text-slate-500">Service Area</span><p class="font-medium">{{ $provider->service_area ?? '—' }}</p></div>
                <div><span class="text-slate-500">Total Jobs</span><p class="font-medium">{{ $provider->total_jobs }}</p></div>
                <div><span class="text-slate-500">Rating</span><p class="font-medium text-amber-600">{{ number_format($provider->rating_avg, 1) }} ★</p></div>
                <div><span class="text-slate-500">Total Earnings</span><p class="font-medium text-emerald-600">₹{{ number_format($provider->total_earnings) }}</p></div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h3 class="font-semibold text-slate-900">Recent Bookings</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500">
                        <tr>
                            <th class="text-left px-6 py-3 font-medium">Booking #</th>
                            <th class="text-left px-6 py-3 font-medium">Service</th>
                            <th class="text-left px-6 py-3 font-medium">Date</th>
                            <th class="text-left px-6 py-3 font-medium">Status</th>
                            <th class="text-left px-6 py-3 font-medium">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($provider->bookings->take(10) as $booking)
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-6 py-3">
                                <a href="{{ route('admin.bookings.show', $booking) }}" class="font-medium text-cyan-600 hover:underline">{{ $booking->booking_number }}</a>
                            </td>
                            <td class="px-6 py-3 text-slate-600">{{ $booking->service?->name }}</td>
                            <td class="px-6 py-3 text-slate-600">{{ $booking->scheduled_date->format('d M Y') }}</td>
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

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h3 class="font-semibold text-slate-900">Payouts</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500">
                        <tr>
                            <th class="text-left px-6 py-3 font-medium">Period</th>
                            <th class="text-left px-6 py-3 font-medium">Amount</th>
                            <th class="text-left px-6 py-3 font-medium">Status</th>
                            <th class="text-left px-6 py-3 font-medium">Paid At</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($provider->payouts as $payout)
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-6 py-3 text-slate-600">{{ $payout->period_start->format('d M') }} – {{ $payout->period_end->format('d M Y') }}</td>
                            <td class="px-6 py-3 font-medium text-emerald-600">₹{{ number_format($payout->amount) }}</td>
                            <td class="px-6 py-3">@include('admin.partials.status-badge', ['status' => $payout->status])</td>
                            <td class="px-6 py-3 text-slate-600">{{ $payout->paid_at?->format('d M Y') ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="px-6 py-8 text-center text-slate-500">No payouts recorded</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h3 class="font-semibold text-slate-900">Leave Requests</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500">
                        <tr>
                            <th class="text-left px-6 py-3 font-medium">Dates</th>
                            <th class="text-left px-6 py-3 font-medium">Reason</th>
                            <th class="text-left px-6 py-3 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($provider->leaveRequests as $leave)
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-6 py-3 text-slate-600">{{ $leave->start_date->format('d M') }} – {{ $leave->end_date->format('d M Y') }}</td>
                            <td class="px-6 py-3 text-slate-600 max-w-xs truncate">{{ $leave->reason }}</td>
                            <td class="px-6 py-3">@include('admin.partials.status-badge', ['status' => $leave->status])</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="px-6 py-8 text-center text-slate-500">No leave requests</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
            <h3 class="font-semibold text-slate-900 mb-4">Quick Stats</h3>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between"><span class="text-slate-500">Bookings</span><span class="font-semibold">{{ $provider->bookings->count() }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Payouts</span><span class="font-semibold">{{ $provider->payouts->count() }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Feedback</span><span class="font-semibold">{{ $provider->feedback->count() }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Leave Requests</span><span class="font-semibold">{{ $provider->leaveRequests->count() }}</span></div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
            <h3 class="font-semibold text-slate-900 mb-4">Actions</h3>
            <div class="space-y-2">
                <a href="{{ route('admin.providers.edit', $provider) }}" class="block w-full text-center py-2 border border-slate-300 text-slate-700 text-sm font-medium rounded-xl hover:bg-slate-50">Edit Provider</a>
                <form method="POST" action="{{ route('admin.providers.resend-credentials', $provider) }}">
                    @csrf
                    <button type="submit" class="w-full py-2 bg-blue-50 text-blue-700 text-sm font-medium rounded-xl hover:bg-blue-100">Resend Login Email</button>
                </form>
                <form method="POST" action="{{ route('admin.providers.toggle-status', $provider) }}">
                    @csrf
                    <button type="submit" class="w-full py-2 {{ $provider->is_active ? 'bg-red-50 text-red-700 hover:bg-red-100' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }} text-sm font-medium rounded-xl">
                        {{ $provider->is_active ? 'Disable Account' : 'Enable Account' }}
                    </button>
                </form>
                <a href="{{ route('admin.bookings.create') }}?provider_id={{ $provider->id }}" class="block w-full text-center py-2 bg-cyan-50 text-cyan-700 text-sm font-medium rounded-xl hover:bg-cyan-100">Assign New Job</a>
                <a href="{{ route('admin.payouts.create') }}?provider_id={{ $provider->id }}" class="block w-full text-center py-2 border border-slate-200 text-slate-700 text-sm font-medium rounded-xl hover:bg-slate-50">Create Payout</a>
                <form method="POST" action="{{ route('admin.providers.destroy', $provider) }}" onsubmit="return confirm('Delete provider {{ $provider->name }}? Bookings will stay, but this provider will be removed.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full py-2 border border-red-200 text-red-600 text-sm font-medium rounded-xl hover:bg-red-50">Delete Provider</button>
                </form>
                <a href="{{ route('admin.providers.index') }}" class="block w-full text-center py-2 text-cyan-600 text-sm font-medium hover:underline">← Back to List</a>
            </div>
        </div>
    </div>
</div>
@endsection
