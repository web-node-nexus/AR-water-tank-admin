@extends('layouts.admin')

@section('title', 'Leave Requests')
@section('page-title', 'Leave Requests')
@section('page-subtitle', 'Review and approve provider leave requests')

@section('content')
<div class="space-y-4">
    <form method="GET" class="flex flex-wrap gap-2">
        <select name="status" class="rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-cyan-500 outline-none">
            <option value="">All Status</option>
            @foreach(['pending', 'approved', 'rejected'] as $s)
                <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-4 py-2 bg-slate-900 text-white text-sm font-medium rounded-xl hover:bg-slate-800">Filter</button>
    </form>

    <div class="space-y-4">
        @forelse($leaves as $leave)
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4 mb-4">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <a href="{{ route('admin.providers.show', $leave->provider) }}" class="font-semibold text-cyan-600 hover:underline">{{ $leave->provider?->name }}</a>
                        @include('admin.partials.status-badge', ['status' => $leave->status])
                    </div>
                    <p class="text-sm text-slate-600">
                        {{ $leave->start_date->format('d M Y') }} – {{ $leave->end_date->format('d M Y') }}
                        <span class="text-slate-400">({{ $leave->start_date->diffInDays($leave->end_date) + 1 }} day(s))</span>
                    </p>
                    <p class="text-sm text-slate-700 mt-2">{{ $leave->reason }}</p>
                    @if($leave->admin_notes)
                    <p class="text-xs text-slate-500 mt-2">Admin notes: {{ $leave->admin_notes }}</p>
                    @endif
                    @if($leave->reviewed_at)
                    <p class="text-xs text-slate-400 mt-1">Reviewed {{ $leave->reviewed_at->format('d M Y') }}</p>
                    @endif
                </div>
            </div>

            @if($leave->status === 'pending')
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 pt-4 border-t border-slate-100">
                <form method="POST" action="{{ route('admin.leaves.approve', $leave) }}" class="space-y-3">
                    @csrf
                    <textarea name="admin_notes" rows="2" placeholder="Optional approval notes..." class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-cyan-500 outline-none"></textarea>
                    <button type="submit" class="w-full py-2 bg-emerald-600 text-white text-sm font-medium rounded-xl hover:bg-emerald-700">Approve</button>
                </form>
                <form method="POST" action="{{ route('admin.leaves.reject', $leave) }}" class="space-y-3">
                    @csrf
                    <textarea name="admin_notes" rows="2" required placeholder="Reason for rejection..." class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-cyan-500 outline-none"></textarea>
                    <button type="submit" class="w-full py-2 bg-red-600 text-white text-sm font-medium rounded-xl hover:bg-red-700">Reject</button>
                </form>
            </div>
            @endif
        </div>
        @empty
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-12 text-center text-slate-500">
            No leave requests found
        </div>
        @endforelse
    </div>

    @if($leaves->hasPages())
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm px-6 py-4">{{ $leaves->links() }}</div>
    @endif
</div>
@endsection
