@extends('layouts.admin')

@section('title', 'Edit ' . $provider->name)
@section('page-title', 'Edit Provider')
@section('page-subtitle', $provider->name)

@section('content')
<div class="max-w-2xl">
    <form method="POST" action="{{ route('admin.providers.update', $provider) }}" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-6">
        @csrf @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Name *</label>
                <input type="text" name="name" value="{{ old('name', $provider->name) }}" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 outline-none">
                @error('name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Phone *</label>
                <input type="text" name="phone" value="{{ old('phone', $provider->phone) }}" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 outline-none">
                @error('phone')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Email (Login ID) *</label>
            <input type="email" name="email" value="{{ old('email', $provider->email) }}" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 outline-none">
            @error('email')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">New Password</label>
                <input type="password" name="password" placeholder="Leave blank to keep current" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 outline-none">
                @error('password')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Confirm New Password</label>
                <input type="password" name="password_confirmation" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 outline-none">
            </div>
        </div>
        <p class="text-xs text-slate-500 -mt-2">If you set a new password, it will be emailed to the provider.</p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Zone</label>
                <select name="zone_id" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 outline-none">
                    <option value="">Select Zone</option>
                    @foreach($zones as $zone)
                        <option value="{{ $zone->id }}" @selected(old('zone_id', $provider->zone_id) == $zone->id)>{{ $zone->name }} ({{ $zone->city }})</option>
                    @endforeach
                </select>
                @error('zone_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Service Area</label>
                <input type="text" name="service_area" value="{{ old('service_area', $provider->service_area) }}" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 outline-none">
                @error('service_area')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Availability Status *</label>
            <select name="availability_status" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 outline-none">
                @foreach(['available', 'busy', 'unavailable'] as $status)
                    <option value="{{ $status }}" @selected(old('availability_status', $provider->availability_status) === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            @error('availability_status')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <label class="flex items-center gap-2 text-sm text-slate-700">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $provider->is_active)) class="rounded text-cyan-600 focus:ring-cyan-500">
            Account enabled (provider can log in)
        </label>
        <div class="flex gap-3 pt-2">
            <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-cyan-600 to-blue-600 text-white font-semibold rounded-xl hover:from-cyan-700 hover:to-blue-700 shadow-lg shadow-cyan-500/25">Update Provider</button>
            <a href="{{ route('admin.providers.show', $provider) }}" class="px-6 py-2.5 border border-slate-300 text-slate-700 font-medium rounded-xl hover:bg-slate-50">Cancel</a>
        </div>
    </form>
</div>
@endsection
