@extends('layouts.admin')

@section('title', 'Edit ' . $zone->name)
@section('page-title', 'Edit Zone')
@section('page-subtitle', $zone->name)

@section('content')
<div class="max-w-2xl">
    <form method="POST" action="{{ route('admin.zones.update', $zone) }}" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-6">
        @csrf @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Code *</label>
                <input type="text" name="code" value="{{ old('code', $zone->code) }}" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 outline-none uppercase">
                @error('code')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Name *</label>
                <input type="text" name="name" value="{{ old('name', $zone->name) }}" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 outline-none">
                @error('name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">City *</label>
            <input type="text" name="city" value="{{ old('city', $zone->city) }}" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 outline-none">
            @error('city')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Pincodes</label>
            <input type="text" name="pincodes" value="{{ old('pincodes', $zone->pincodes ? implode(', ', $zone->pincodes) : '') }}" placeholder="110042, 110043, 110044" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 outline-none">
            <p class="text-xs text-slate-500 mt-1">Comma-separated list of pincodes</p>
            @error('pincodes')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <label class="flex items-center gap-2 text-sm text-slate-700">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $zone->is_active)) class="rounded text-cyan-600 focus:ring-cyan-500">
            Active zone
        </label>
        <div class="flex gap-3 pt-2">
            <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-cyan-600 to-blue-600 text-white font-semibold rounded-xl hover:from-cyan-700 hover:to-blue-700 shadow-lg shadow-cyan-500/25">Update Zone</button>
            <a href="{{ route('admin.zones.index') }}" class="px-6 py-2.5 border border-slate-300 text-slate-700 font-medium rounded-xl hover:bg-slate-50">Cancel</a>
        </div>
    </form>
</div>
@endsection
