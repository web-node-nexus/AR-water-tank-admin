@extends('layouts.admin')

@section('title', 'New Booking')
@section('page-title', 'Create New Booking')
@section('page-subtitle', 'Manually enter a customer booking')

@section('content')
<div class="max-w-3xl">
    <form method="POST" action="{{ route('admin.bookings.store') }}" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-6">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Customer Name *</label>
                <input type="text" name="customer_name" value="{{ old('customer_name') }}" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 outline-none">
                @error('customer_name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Phone *</label>
                <input type="text" name="customer_phone" value="{{ old('customer_phone') }}" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 outline-none">
                @error('customer_phone')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Address *</label>
            <textarea name="customer_address" rows="2" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 outline-none">{{ old('customer_address') }}</textarea>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Latitude</label>
                <input type="number" name="latitude" value="{{ old('latitude') }}" step="any" placeholder="e.g. 28.7041000" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 outline-none">
                @error('latitude')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Longitude</label>
                <input type="number" name="longitude" value="{{ old('longitude') }}" step="any" placeholder="e.g. 77.1025000" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 outline-none">
                @error('longitude')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>
        <p class="text-xs text-slate-400 -mt-3">Optional. Provider app will open Google Maps directions to these coordinates.</p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Pincode</label>
                <input type="text" name="pincode" value="{{ old('pincode') }}" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Zone</label>
                <select name="zone_id" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 outline-none">
                    <option value="">Select Zone</option>
                    @foreach($zones as $zone)
                        <option value="{{ $zone->id }}" @selected(old('zone_id') == $zone->id)>{{ $zone->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Service *</label>
            <select name="service_id" id="service_id" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 outline-none">
                <option value="">Select Service</option>
                @foreach($services as $service)
                    <option value="{{ $service->id }}" data-price="{{ $service->base_price }}" @selected(old('service_id') == $service->id)>{{ $service->name }} — ₹{{ number_format($service->base_price) }}</option>
                @endforeach
            </select>
            <p class="text-xs text-slate-400 mt-1">Selecting a service auto-fills the amount. You can edit it below.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Tank Type</label>
                <input type="text" name="tank_type" value="{{ old('tank_type') }}" placeholder="Overhead / Underground" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Tank Size</label>
                <input type="text" name="tank_size" value="{{ old('tank_size') }}" placeholder="e.g. 1000L" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Amount (₹) *</label>
                <input type="number" name="amount" id="amount" value="{{ old('amount') }}" step="0.01" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 outline-none">
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Scheduled Date *</label>
                <input type="date" name="scheduled_date" value="{{ old('scheduled_date', date('Y-m-d')) }}" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Scheduled Time</label>
                <input type="time" name="scheduled_time" value="{{ old('scheduled_time', '10:00') }}" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Assign Provider</label>
                <select name="provider_id" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 outline-none">
                    <option value="">Assign Later</option>
                    @foreach($providers as $provider)
                        <option value="{{ $provider->id }}" @selected(old('provider_id') == $provider->id)>{{ $provider->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Special Notes</label>
            <textarea name="special_notes" rows="2" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 outline-none">{{ old('special_notes') }}</textarea>
        </div>
        <div class="flex gap-3 pt-2">
            <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-cyan-600 to-blue-600 text-white font-semibold rounded-xl hover:from-cyan-700 hover:to-blue-700 shadow-lg shadow-cyan-500/25">Create Booking</button>
            <a href="{{ route('admin.bookings.index') }}" class="px-6 py-2.5 border border-slate-300 text-slate-700 font-medium rounded-xl hover:bg-slate-50">Cancel</a>
        </div>
    </form>
</div>

<script>
document.getElementById('service_id').addEventListener('change', function() {
    const price = this.options[this.selectedIndex]?.dataset?.price;
    if (price) document.getElementById('amount').value = price;
});
</script>
@endsection
