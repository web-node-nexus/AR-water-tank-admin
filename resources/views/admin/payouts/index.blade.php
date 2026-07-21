@extends('layouts.admin')

@section('title', 'Payouts')
@section('page-title', 'Provider Payouts')
@section('page-subtitle', 'Track and process provider payments')

@section('content')
<div class="space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <form method="GET" class="flex flex-wrap gap-2">
            <select name="status" class="rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-cyan-500 outline-none">
                <option value="">All Status</option>
                @foreach(['pending', 'paid'] as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-slate-900 text-white text-sm font-medium rounded-xl hover:bg-slate-800">Filter</button>
        </form>
        <a href="{{ route('admin.payouts.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-cyan-600 to-blue-600 text-white text-sm font-semibold rounded-xl hover:from-cyan-700 hover:to-blue-700 shadow-lg shadow-cyan-500/25">
            + Create Payout
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="text-left px-6 py-3 font-medium">Provider</th>
                        <th class="text-left px-6 py-3 font-medium">Period</th>
                        <th class="text-left px-6 py-3 font-medium">Amount</th>
                        <th class="text-left px-6 py-3 font-medium">Status</th>
                        <th class="text-left px-6 py-3 font-medium">Paid At</th>
                        <th class="text-left px-6 py-3 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($payouts as $payout)
                    <tr class="hover:bg-slate-50/50">
                        <td class="px-6 py-3">
                            <a href="{{ route('admin.providers.show', $payout->provider) }}" class="font-medium text-cyan-600 hover:underline">{{ $payout->provider?->name }}</a>
                        </td>
                        <td class="px-6 py-3 text-slate-600">{{ $payout->period_start->format('d M') }} – {{ $payout->period_end->format('d M Y') }}</td>
                        <td class="px-6 py-3 font-medium text-emerald-600">₹{{ number_format($payout->amount) }}</td>
                        <td class="px-6 py-3">@include('admin.partials.status-badge', ['status' => $payout->status])</td>
                        <td class="px-6 py-3 text-slate-600">{{ $payout->paid_at?->format('d M Y') ?? '—' }}</td>
                        <td class="px-6 py-3">
                            @if($payout->status === 'pending')
                            <form method="POST" action="{{ route('admin.payouts.mark-paid', $payout) }}" class="inline">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 bg-emerald-600 text-white text-xs font-medium rounded-lg hover:bg-emerald-700" onclick="return confirm('Mark this payout as paid?')">Mark Paid</button>
                            </form>
                            @else
                            <span class="text-xs text-slate-400">Processed</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-6 py-12 text-center text-slate-500">No payouts found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($payouts->hasPages())
        <div class="px-6 py-4 border-t border-slate-100">{{ $payouts->links() }}</div>
        @endif
    </div>
</div>
@endsection
