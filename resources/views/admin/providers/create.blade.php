@extends('layouts.admin')

@section('title', 'Add Provider')
@section('page-title', 'Add Service Provider')
@section('page-subtitle', 'Create account — login credentials will be emailed automatically')

@section('content')
<div class="max-w-2xl">
    <div class="mb-4 p-4 bg-cyan-50 border border-cyan-100 rounded-xl text-sm text-cyan-900">
        Provider will receive an email with their <strong>email & password</strong> to log in to the mobile app.
    </div>
    <form method="POST" action="{{ route('admin.providers.store') }}" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-6">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Full Name *</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 outline-none">
                @error('name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Phone *</label>
                <input type="text" name="phone" value="{{ old('phone') }}" required placeholder="9876543210" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 outline-none">
                @error('phone')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Email (Login ID) *</label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="provider@email.com" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 outline-none">
                @error('email')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Password *</label>
                <input type="password" name="password" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 outline-none">
                @error('password')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Confirm Password *</label>
            <input type="password" name="password_confirmation" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 outline-none">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Zone</label>
                <select name="zone_id" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 outline-none">
                    <option value="">Select Zone</option>
                    @foreach($zones as $zone)
                        <option value="{{ $zone->id }}" @selected(old('zone_id') == $zone->id)>{{ $zone->name }} ({{ $zone->city }})</option>
                    @endforeach
                </select>
                @error('zone_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Service Area</label>
                <input type="text" name="service_area" value="{{ old('service_area') }}" placeholder="e.g. North Delhi, Rohini" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 outline-none">
                @error('service_area')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Availability Status *</label>
            <select name="availability_status" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 outline-none">
                @foreach(['available', 'busy', 'unavailable'] as $status)
                    <option value="{{ $status }}" @selected(old('availability_status', 'available') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            @error('availability_status')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <label class="flex items-center gap-2 text-sm text-slate-700">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true)) class="rounded text-cyan-600 focus:ring-cyan-500">
            Enable account immediately
        </label>
        <div class="flex gap-3 pt-2">
            <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-cyan-600 to-blue-600 text-white font-semibold rounded-xl hover:from-cyan-700 hover:to-blue-700 shadow-lg shadow-cyan-500/25">Create & Send Email</button>
            <a href="{{ route('admin.providers.index') }}" class="px-6 py-2.5 border border-slate-300 text-slate-700 font-medium rounded-xl hover:bg-slate-50">Cancel</a>
        </div>
    </form>
</div>
@endsection
