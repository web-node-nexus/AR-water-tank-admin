@extends('layouts.admin')

@section('title', 'Bookings')
@section('page-title', 'Booking Management')
@section('page-subtitle', 'View and manage all customer bookings')

@section('content')
<div class="space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <form method="GET" class="flex flex-wrap gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search booking, customer..."
                class="rounded-xl border border-slate-300 px-4 py-2 text-sm w-64 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 outline-none">
            <select name="status" class="rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-cyan-500 outline-none">
                <option value="">All Status</option>
                @foreach(['pending','assigned','in_progress','completed','cancelled'] as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                @endforeach
            </select>
            <input type="date" name="date" value="{{ request('date') }}" class="rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-cyan-500 outline-none">
            <button type="submit" class="px-4 py-2 bg-slate-900 text-white text-sm font-medium rounded-xl hover:bg-slate-800">Filter</button>
        </form>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.bookings.export', request()->query()) }}" class="inline-flex items-center gap-2 px-4 py-2 border border-slate-300 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-50">
                Export CSV
            </a>
            <a href="{{ route('admin.bookings.import') }}" class="inline-flex items-center gap-2 px-4 py-2 border border-cyan-600 text-cyan-700 text-sm font-semibold rounded-xl hover:bg-cyan-50">
                Upload Excel
            </a>
            <a href="{{ route('admin.bookings.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-cyan-600 to-blue-600 text-white text-sm font-semibold rounded-xl hover:from-cyan-700 hover:to-blue-700 shadow-lg shadow-cyan-500/25">
                + New Booking
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="text-left px-6 py-3 font-medium">Booking #</th>
                        <th class="text-left px-6 py-3 font-medium">Customer</th>
                        <th class="text-left px-6 py-3 font-medium">Service</th>
                        <th class="text-left px-6 py-3 font-medium">Date</th>
                        <th class="text-left px-6 py-3 font-medium">Provider</th>
                        <th class="text-left px-6 py-3 font-medium">Status</th>
                        <th class="text-left px-6 py-3 font-medium">Amount</th>
                        <th class="text-left px-6 py-3 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($bookings as $booking)
                    <tr class="hover:bg-slate-50/50">
                        <td class="px-6 py-3 font-medium text-cyan-600">{{ $booking->booking_number }}</td>
                        <td class="px-6 py-3">
                            <p class="font-medium text-slate-900">{{ $booking->customer_name }}</p>
                            <p class="text-xs text-slate-500">{{ $booking->customer_phone }}</p>
                        </td>
                        <td class="px-6 py-3 text-slate-600 max-w-[180px] truncate">{{ $booking->service?->name }}</td>
                        <td class="px-6 py-3 text-slate-600">{{ $booking->scheduled_date->format('d M Y') }}</td>
                        <td class="px-6 py-3 text-slate-600">{{ $booking->provider?->name ?? '—' }}</td>
                        <td class="px-6 py-3">@include('admin.partials.status-badge', ['status' => $booking->status])</td>
                        <td class="px-6 py-3 font-medium">₹{{ number_format($booking->amount) }}</td>
                        <td class="px-6 py-3">
                            <div class="flex items-center gap-3">
                                <a href="{{ route('admin.bookings.show', $booking) }}" class="text-cyan-600 hover:underline text-xs font-medium">View</a>
                                <form method="POST" action="{{ route('admin.bookings.destroy', $booking) }}" onsubmit="return confirm('Delete booking {{ $booking->booking_number }}? This cannot be undone.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline text-xs font-medium">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="px-6 py-12 text-center text-slate-500">No bookings found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($bookings->hasPages())
        <div class="px-6 py-4 border-t border-slate-100">{{ $bookings->links() }}</div>
        @endif
    </div>
</div>
@endsection
