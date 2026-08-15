@extends('layouts.admin')

@section('title', 'Booking ' . $booking->booking_number)
@section('page-title', 'Booking Details')
@section('page-subtitle', $booking->booking_number)

@section('content')
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <div class="xl:col-span-2 space-y-6">
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
            <div class="flex items-start justify-between mb-6">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">{{ $booking->customer_name }}</h2>
                    <p class="text-sm text-slate-500">{{ $booking->customer_phone }}</p>
                </div>
                @include('admin.partials.status-badge', ['status' => $booking->status])
            </div>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div><span class="text-slate-500">Service</span><p class="font-medium">{{ $booking->service?->name }}</p></div>
                <div><span class="text-slate-500">Amount</span><p class="font-medium text-emerald-600">₹{{ number_format($booking->amount) }}</p></div>
                <div><span class="text-slate-500">Scheduled</span><p class="font-medium">{{ $booking->scheduled_date->format('d M Y') }} {{ $booking->scheduled_time ? substr($booking->scheduled_time, 0, 5) : '' }}</p></div>
                <div><span class="text-slate-500">Payment</span><p class="font-medium capitalize">{{ $booking->payment_status }}</p></div>
                <div><span class="text-slate-500">Tank</span><p class="font-medium">{{ $booking->tank_type ?? '—' }} {{ $booking->tank_size ? '('.$booking->tank_size.')' : '' }}</p></div>
                <div><span class="text-slate-500">Provider</span><p class="font-medium">{{ $booking->provider?->name ?? 'Not assigned' }}</p></div>
                <div class="col-span-2"><span class="text-slate-500">Address</span><p class="font-medium">{{ $booking->customer_address }}</p></div>
                @if($booking->latitude && $booking->longitude)
                <div class="col-span-2">
                    <span class="text-slate-500">Location (Lat / Long)</span>
                    <p class="font-medium">
                        <a href="{{ $booking->mapsUrl() }}" target="_blank" rel="noopener" class="text-cyan-600 hover:underline">
                            {{ $booking->latitude }}, {{ $booking->longitude }}
                        </a>
                        <span class="text-xs text-slate-400 ml-2">Open in Google Maps</span>
                    </p>
                </div>
                @endif
                @if($booking->special_notes)
                <div class="col-span-2"><span class="text-slate-500">Notes</span><p class="font-medium">{{ $booking->special_notes }}</p></div>
                @endif
            </div>
        </div>

        @if($booking->photos->count())
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
            <h3 class="font-semibold text-slate-900 mb-4">Job Photos</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                @foreach($booking->photos as $photo)
                <div class="rounded-xl overflow-hidden border border-slate-200">
                    <img src="{{ asset('storage/'.$photo->file_path) }}" alt="{{ $photo->type }}" class="w-full h-32 object-cover">
                    <p class="text-xs text-center py-1 capitalize bg-slate-50">{{ $photo->type }}</p>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <div class="space-y-6">
        @if(!$booking->provider_id && !in_array($booking->status->value, ['completed', 'cancelled']))
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
            <h3 class="font-semibold text-slate-900 mb-4">Assign Provider</h3>
            <form method="POST" action="{{ route('admin.bookings.assign', $booking) }}" class="space-y-3">
                @csrf
                <select name="provider_id" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-cyan-500 outline-none">
                    <option value="">Select Provider</option>
                    @foreach($providers as $provider)
                        <option value="{{ $provider->id }}">{{ $provider->name }} ({{ $provider->availability_status }})</option>
                    @endforeach
                </select>
                <button type="submit" class="w-full py-2 bg-cyan-600 text-white text-sm font-medium rounded-xl hover:bg-cyan-700">Assign</button>
            </form>
        </div>
        @endif

        @if(!in_array($booking->status->value, ['completed', 'cancelled']))
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
            <h3 class="font-semibold text-slate-900 mb-4">Cancel Booking</h3>
            <form method="POST" action="{{ route('admin.bookings.cancel', $booking) }}" class="space-y-3">
                @csrf
                <textarea name="cancellation_reason" rows="2" required placeholder="Reason for cancellation" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-cyan-500 outline-none"></textarea>
                <button type="submit" class="w-full py-2 bg-red-600 text-white text-sm font-medium rounded-xl hover:bg-red-700" onclick="return confirm('Cancel this booking?')">Cancel Booking</button>
            </form>
        </div>
        @endif

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
            <h3 class="font-semibold text-slate-900 mb-4">Actions</h3>
            <div class="space-y-2">
                <a href="{{ route('admin.bookings.edit', $booking) }}" class="block w-full text-center py-2 border border-slate-300 text-slate-700 text-sm font-medium rounded-xl hover:bg-slate-50">Edit Booking</a>
                <form method="POST" action="{{ route('admin.bookings.destroy', $booking) }}" onsubmit="return confirm('Delete booking {{ $booking->booking_number }}? This cannot be undone.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="block w-full py-2 border border-red-200 text-red-600 text-sm font-medium rounded-xl hover:bg-red-50">Delete Booking</button>
                </form>
                <a href="{{ route('admin.bookings.index') }}" class="block w-full text-center py-2 text-cyan-600 text-sm font-medium hover:underline">← Back to List</a>
            </div>
        </div>
    </div>
</div>
@endsection
