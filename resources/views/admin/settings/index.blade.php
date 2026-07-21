@extends('layouts.admin')

@section('title', 'Settings')
@section('page-title', 'Company Settings')
@section('page-subtitle', 'Manage company information and policies')

@section('content')
<div class="max-w-3xl">
    <form method="POST" action="{{ route('admin.settings.update') }}" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-6">
        @csrf @method('PUT')

        <div>
            <h3 class="text-sm font-semibold text-slate-900 mb-4 pb-2 border-b border-slate-100">Company Information</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Company Name *</label>
                    <input type="text" name="company_name" value="{{ old('company_name', $settings['company_name']) }}" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 outline-none">
                    @error('company_name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Phone *</label>
                        <input type="text" name="company_phone" value="{{ old('company_phone', $settings['company_phone']) }}" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 outline-none">
                        @error('company_phone')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Email *</label>
                        <input type="email" name="company_email" value="{{ old('company_email', $settings['company_email']) }}" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 outline-none">
                        @error('company_email')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Address *</label>
                    <textarea name="company_address" rows="2" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 outline-none">{{ old('company_address', $settings['company_address']) }}</textarea>
                    @error('company_address')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">WhatsApp Number</label>
                    <input type="text" name="whatsapp_number" value="{{ old('whatsapp_number', $settings['whatsapp_number']) }}" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 outline-none">
                    @error('whatsapp_number')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <div>
            <h3 class="text-sm font-semibold text-slate-900 mb-4 pb-2 border-b border-slate-100">Booking Settings</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Slot Start Time *</label>
                    <input type="time" name="booking_slot_start" value="{{ old('booking_slot_start', $settings['booking_slot_start']) }}" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 outline-none">
                    @error('booking_slot_start')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Slot End Time *</label>
                    <input type="time" name="booking_slot_end" value="{{ old('booking_slot_end', $settings['booking_slot_end']) }}" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 outline-none">
                    @error('booking_slot_end')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <div>
            <h3 class="text-sm font-semibold text-slate-900 mb-4 pb-2 border-b border-slate-100">Policies</h3>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Cancellation Policy</label>
                <textarea name="cancellation_policy" rows="4" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 outline-none">{{ old('cancellation_policy', $settings['cancellation_policy']) }}</textarea>
                @error('cancellation_policy')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-cyan-600 to-blue-600 text-white font-semibold rounded-xl hover:from-cyan-700 hover:to-blue-700 shadow-lg shadow-cyan-500/25">Save Settings</button>
        </div>
    </form>
</div>
@endsection
