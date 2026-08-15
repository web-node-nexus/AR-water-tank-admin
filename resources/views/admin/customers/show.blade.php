@extends('layouts.admin')

@section('title', $customer->name)
@section('page-title', 'Customer Details')
@section('page-subtitle', $customer->name)

@section('content')
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <div class="xl:col-span-2 space-y-6">
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
            <div class="flex items-start justify-between mb-6">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">{{ $customer->name }}</h2>
                    <p class="text-sm text-slate-500">{{ $customer->phone }} @if($customer->email) · {{ $customer->email }} @endif</p>
                </div>
                <div class="text-right">
                    <p class="text-lg font-bold text-emerald-600">₹{{ number_format($customer->total_spent ?? 0) }}</p>
                    <p class="text-xs text-slate-500">Total spent</p>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div><span class="text-slate-500">Address</span><p class="font-medium">{{ $customer->address ?? '—' }}</p></div>
                <div><span class="text-slate-500">Pincode</span><p class="font-medium">{{ $customer->pincode ?? '—' }}</p></div>
                @if($customer->notes)
                <div class="col-span-2"><span class="text-slate-500">Notes</span><p class="font-medium">{{ $customer->notes }}</p></div>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h3 class="font-semibold text-slate-900">Booking History</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500">
                        <tr>
                            <th class="text-left px-6 py-3 font-medium">Booking #</th>
                            <th class="text-left px-6 py-3 font-medium">Service</th>
                            <th class="text-left px-6 py-3 font-medium">Date</th>
                            <th class="text-left px-6 py-3 font-medium">Provider</th>
                            <th class="text-left px-6 py-3 font-medium">Status</th>
                            <th class="text-left px-6 py-3 font-medium">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($customer->bookings as $booking)
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-6 py-3">
                                <a href="{{ route('admin.bookings.show', $booking) }}" class="font-medium text-cyan-600 hover:underline">{{ $booking->booking_number }}</a>
                            </td>
                            <td class="px-6 py-3 text-slate-600">{{ $booking->service?->name }}</td>
                            <td class="px-6 py-3 text-slate-600">{{ $booking->scheduled_date->format('d M Y') }}</td>
                            <td class="px-6 py-3 text-slate-600">{{ $booking->provider?->name ?? '—' }}</td>
                            <td class="px-6 py-3">@include('admin.partials.status-badge', ['status' => $booking->status])</td>
                            <td class="px-6 py-3 font-medium">₹{{ number_format($booking->amount) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-6 py-8 text-center text-slate-500">No bookings yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($customer->feedback->count())
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
            <h3 class="font-semibold text-slate-900 mb-4">Feedback Given</h3>
            <div class="space-y-3">
                @foreach($customer->feedback as $item)
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-amber-600 font-medium">{{ $item->rating }} ★</span>
                        <span class="text-xs text-slate-500">{{ $item->created_at->format('d M Y') }}</span>
                    </div>
                    @if($item->review)
                    <p class="text-sm text-slate-600">{{ $item->review }}</p>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
            <h3 class="font-semibold text-slate-900 mb-4">Summary</h3>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between"><span class="text-slate-500">Total Bookings</span><span class="font-semibold">{{ $customer->bookings->count() }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Feedback</span><span class="font-semibold">{{ $customer->feedback->count() }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Member Since</span><span class="font-semibold">{{ $customer->created_at->format('M Y') }}</span></div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
            <h3 class="font-semibold text-slate-900 mb-4">Actions</h3>
            <div class="space-y-2">
                <a href="{{ route('admin.customers.edit', $customer) }}" class="block w-full text-center py-2 border border-slate-300 text-slate-700 text-sm font-medium rounded-xl hover:bg-slate-50">Edit Customer</a>
                <a href="{{ route('admin.bookings.create') }}" class="block w-full text-center py-2 bg-cyan-50 text-cyan-700 text-sm font-medium rounded-xl hover:bg-cyan-100">New Booking</a>
                <form method="POST" action="{{ route('admin.customers.destroy', $customer) }}" onsubmit="return confirm('Delete customer {{ $customer->name }}? Bookings will stay, but this customer record will be removed.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full py-2 border border-red-200 text-red-600 text-sm font-medium rounded-xl hover:bg-red-50">Delete Customer</button>
                </form>
                <a href="{{ route('admin.customers.index') }}" class="block w-full text-center py-2 text-cyan-600 text-sm font-medium hover:underline">← Back to List</a>
            </div>
        </div>
    </div>
</div>
@endsection
