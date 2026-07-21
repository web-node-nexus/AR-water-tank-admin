@extends('layouts.admin')

@section('title', 'Edit Booking')
@section('page-title', 'Edit Booking')
@section('page-subtitle', $booking->booking_number)

@section('content')
<div class="max-w-3xl">
    <form method="POST" action="{{ route('admin.bookings.update', $booking) }}" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-6">
        @csrf @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Customer Name</label>
                <input type="text" name="customer_name" value="{{ old('customer_name', $booking->customer_name) }}" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Phone</label>
                <input type="text" name="customer_phone" value="{{ old('customer_phone', $booking->customer_phone) }}" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 outline-none">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Address</label>
            <textarea name="customer_address" rows="2" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 outline-none">{{ old('customer_address', $booking->customer_address) }}</textarea>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Service</label>
                <select name="service_id" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 outline-none">
                    @foreach($services as $service)
                        <option value="{{ $service->id }}" @selected(old('service_id', $booking->service_id) == $service->id)>{{ $service->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                <select name="status" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 outline-none">
                    @foreach(['pending','assigned','in_progress','completed','cancelled'] as $s)
                        <option value="{{ $s }}" @selected(old('status', $booking->status->value) === $s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Amount (₹)</label>
                <input type="number" name="amount" value="{{ old('amount', $booking->amount) }}" step="0.01" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Payment Status</label>
                <select name="payment_status" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 outline-none">
                    @foreach(['pending','paid','refunded'] as $ps)
                        <option value="{{ $ps }}" @selected(old('payment_status', $booking->payment_status) === $ps)>{{ ucfirst($ps) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Provider</label>
                <select name="provider_id" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 outline-none">
                    <option value="">None</option>
                    @foreach($providers as $provider)
                        <option value="{{ $provider->id }}" @selected(old('provider_id', $booking->provider_id) == $provider->id)>{{ $provider->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Scheduled Date</label>
                <input type="date" name="scheduled_date" value="{{ old('scheduled_date', $booking->scheduled_date->format('Y-m-d')) }}" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Scheduled Time</label>
                <input type="time" name="scheduled_time" value="{{ old('scheduled_time', $booking->scheduled_time ? substr($booking->scheduled_time, 0, 5) : '') }}" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 outline-none">
            </div>
        </div>
        <input type="hidden" name="pincode" value="{{ $booking->pincode }}">
        <input type="hidden" name="zone_id" value="{{ $booking->zone_id }}">
        <input type="hidden" name="pricing_slab_id" value="{{ $booking->pricing_slab_id }}">
        <input type="hidden" name="tank_type" value="{{ $booking->tank_type }}">
        <input type="hidden" name="tank_size" value="{{ $booking->tank_size }}">
        <input type="hidden" name="special_notes" value="{{ $booking->special_notes }}">
        <div class="flex gap-3">
            <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-cyan-600 to-blue-600 text-white font-semibold rounded-xl">Update Booking</button>
            <a href="{{ route('admin.bookings.show', $booking) }}" class="px-6 py-2.5 border border-slate-300 text-slate-700 font-medium rounded-xl hover:bg-slate-50">Cancel</a>
        </div>
    </form>
</div>
@endsection
