@extends('layouts.admin')
@section('title', 'Services')
@section('page-title', 'Services & Pricing')
@section('page-subtitle', 'Manage service types and pricing slabs')
@section('content')
<div class="space-y-4">
    <div class="flex justify-end">
        <a href="{{ route('admin.services.create') }}" class="px-4 py-2 bg-gradient-to-r from-cyan-600 to-blue-600 text-white text-sm font-semibold rounded-xl shadow-lg shadow-cyan-500/25">+ Add Service</a>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($services as $service)
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 hover:shadow-md transition">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <span class="text-xs font-medium text-cyan-600 bg-cyan-50 px-2 py-0.5 rounded-full">{{ $service->category }}</span>
                    <h3 class="font-semibold text-slate-900 mt-2">{{ $service->name }}</h3>
                </div>
                <div class="text-right">
                    <p class="text-lg font-bold text-emerald-600">₹{{ number_format($service->base_price) }}</p>
                    @if($service->is_featured)<span class="text-xs text-amber-600">★ Featured</span>@endif
                </div>
            </div>
            <p class="text-sm text-slate-500 mb-3">{{ $service->pricing_slabs_count }} pricing slab(s)</p>
            <div class="flex gap-2">
                <a href="{{ route('admin.services.edit', $service) }}" class="text-sm text-cyan-600 font-medium hover:underline">Edit</a>
                <span class="text-slate-300">|</span>
                <span class="text-xs {{ $service->is_active ? 'text-emerald-600' : 'text-red-500' }}">{{ $service->is_active ? 'Active' : 'Inactive' }}</span>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
