@extends('layouts.admin')

@section('title', 'Feedback')
@section('page-title', 'Customer Feedback')
@section('page-subtitle', 'Review ratings and respond to feedback')

@section('content')
<div class="space-y-4">
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.feedback.index') }}" class="px-4 py-2 text-sm font-medium rounded-xl {{ !request('flagged') ? 'bg-cyan-600 text-white' : 'bg-white border border-slate-300 text-slate-700 hover:bg-slate-50' }}">All</a>
        <a href="{{ route('admin.feedback.index', ['flagged' => 1]) }}" class="px-4 py-2 text-sm font-medium rounded-xl {{ request('flagged') ? 'bg-red-600 text-white' : 'bg-white border border-slate-300 text-slate-700 hover:bg-slate-50' }}">Flagged Only</a>
    </div>

    <div class="space-y-4">
        @forelse($feedback as $item)
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 {{ $item->is_flagged ? 'ring-2 ring-red-200' : '' }}">
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4 mb-4">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <span class="text-lg font-bold text-amber-600">{{ $item->rating }} ★</span>
                        @if($item->is_flagged)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">Flagged</span>
                        @endif
                    </div>
                    <p class="text-sm text-slate-600">
                        <span class="font-medium text-slate-900">{{ $item->customer?->name ?? 'Unknown' }}</span>
                        · Booking
                        @if($item->booking)
                        <a href="{{ route('admin.bookings.show', $item->booking) }}" class="text-cyan-600 hover:underline">{{ $item->booking->booking_number }}</a>
                        @else
                        —
                        @endif
                        · Provider: {{ $item->provider?->name ?? '—' }}
                    </p>
                    <p class="text-xs text-slate-400 mt-1">{{ $item->created_at->format('d M Y, h:i A') }}</p>
                </div>
                <form method="POST" action="{{ route('admin.feedback.toggle-flag', $item) }}">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 text-xs font-medium rounded-lg border {{ $item->is_flagged ? 'border-slate-300 text-slate-600 hover:bg-slate-50' : 'border-red-300 text-red-600 hover:bg-red-50' }}">
                        {{ $item->is_flagged ? 'Unflag' : 'Flag' }}
                    </button>
                </form>
            </div>

            @if($item->review)
            <div class="mb-4 p-4 rounded-xl bg-slate-50 border border-slate-100">
                <p class="text-sm text-slate-700">{{ $item->review }}</p>
            </div>
            @endif

            @if($item->admin_response)
            <div class="mb-4 p-4 rounded-xl bg-cyan-50 border border-cyan-100">
                <p class="text-xs font-medium text-cyan-700 mb-1">Admin Response</p>
                <p class="text-sm text-slate-700">{{ $item->admin_response }}</p>
            </div>
            @endif

            <form method="POST" action="{{ route('admin.feedback.respond', $item) }}" class="space-y-3">
                @csrf
                <textarea name="admin_response" rows="2" placeholder="Write a response..." required class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 outline-none">{{ old('admin_response', $item->admin_response) }}</textarea>
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" name="is_flagged" value="1" @checked($item->is_flagged) class="rounded text-red-600">
                        Keep flagged
                    </label>
                    <button type="submit" class="px-4 py-2 bg-cyan-600 text-white text-sm font-medium rounded-xl hover:bg-cyan-700">Save Response</button>
                </div>
            </form>
        </div>
        @empty
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-12 text-center text-slate-500">
            No feedback found
        </div>
        @endforelse
    </div>

    @if($feedback->hasPages())
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm px-6 py-4">{{ $feedback->links() }}</div>
    @endif
</div>
@endsection
