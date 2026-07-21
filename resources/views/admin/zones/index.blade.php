@extends('layouts.admin')

@section('title', 'Zones')
@section('page-title', 'Service Zones')
@section('page-subtitle', 'Manage geographic service zones and pincodes')

@section('content')
<div class="space-y-4">
    <div class="flex justify-end">
        <a href="{{ route('admin.zones.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-cyan-600 to-blue-600 text-white text-sm font-semibold rounded-xl hover:from-cyan-700 hover:to-blue-700 shadow-lg shadow-cyan-500/25">
            + Add Zone
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @forelse($zones as $zone)
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 hover:shadow-md transition">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <span class="text-xs font-medium text-cyan-600 bg-cyan-50 px-2 py-0.5 rounded-full">{{ $zone->code }}</span>
                    <h3 class="font-semibold text-slate-900 mt-2">{{ $zone->name }}</h3>
                    <p class="text-sm text-slate-500">{{ $zone->city }}</p>
                </div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $zone->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                    {{ $zone->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
            @if($zone->pincodes)
            <p class="text-xs text-slate-500 mb-3">
                Pincodes: {{ implode(', ', array_slice($zone->pincodes, 0, 5)) }}{{ count($zone->pincodes) > 5 ? '...' : '' }}
            </p>
            @endif
            <p class="text-sm text-slate-500 mb-3">{{ $zone->providers_count }} provider(s)</p>
            <div class="flex gap-2">
                <a href="{{ route('admin.zones.edit', $zone) }}" class="text-sm text-cyan-600 font-medium hover:underline">Edit</a>
            </div>
        </div>
        @empty
        <div class="col-span-full bg-white rounded-2xl border border-slate-200/80 shadow-sm p-12 text-center text-slate-500">
            No zones configured yet
        </div>
        @endforelse
    </div>
</div>
@endsection
