@extends('layouts.admin')
@section('title', 'Edit Service')
@section('page-title', 'Edit Service')
@section('page-subtitle', $service->name)
@section('content')
<div class="max-w-3xl space-y-6">
    <form method="POST" action="{{ route('admin.services.update', $service) }}" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
        @csrf @method('PUT')
        <div><label class="block text-sm font-medium text-slate-700 mb-1">Name *</label><input type="text" name="name" value="{{ old('name', $service->name) }}" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 outline-none"></div>
        <div><label class="block text-sm font-medium text-slate-700 mb-1">Category *</label><input type="text" name="category" value="{{ old('category', $service->category) }}" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 outline-none"></div>
        <div><label class="block text-sm font-medium text-slate-700 mb-1">Description</label><textarea name="description" rows="2" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 outline-none">{{ old('description', $service->description) }}</textarea></div>
        <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-slate-700 mb-1">Base Price (₹)</label><input type="number" name="base_price" value="{{ old('base_price', $service->base_price) }}" step="0.01" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 outline-none"></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1">Sort Order</label><input type="number" name="sort_order" value="{{ old('sort_order', $service->sort_order) }}" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 outline-none"></div>
        </div>
        <div class="flex gap-4">
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked($service->is_active) class="rounded text-cyan-600"> Active</label>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_featured" value="1" @checked($service->is_featured) class="rounded text-cyan-600"> Featured</label>
        </div>
        <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-cyan-600 to-blue-600 text-white font-semibold rounded-xl">Update Service</button>
    </form>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
        <h3 class="font-semibold text-slate-900 mb-4">Pricing Slabs</h3>
        @foreach($service->pricingSlabs as $slab)
        <div class="flex items-center justify-between py-2 border-b border-slate-100 last:border-0">
            <div>
                <p class="font-medium text-sm">{{ $slab->name }}</p>
                <p class="text-xs text-slate-500">{{ $slab->min_capacity }}L - {{ $slab->max_capacity ? $slab->max_capacity.'L' : '∞' }}</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="font-semibold text-emerald-600">₹{{ number_format($slab->sale_price ?? $slab->price) }}</span>
                @if($slab->sale_price)<span class="text-xs text-slate-400 line-through">₹{{ number_format($slab->price) }}</span>@endif
                <form method="POST" action="{{ route('admin.services.slabs.destroy', $slab) }}" onsubmit="return confirm('Delete slab?')">@csrf @method('DELETE')<button class="text-red-500 text-xs">Delete</button></form>
            </div>
        </div>
        @endforeach
        <form method="POST" action="{{ route('admin.services.slabs.store', $service) }}" class="mt-4 grid grid-cols-2 md:grid-cols-5 gap-2">
            @csrf
            <input type="text" name="name" placeholder="Slab name" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <input type="number" name="min_capacity" placeholder="Min L" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <input type="number" name="max_capacity" placeholder="Max L" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <input type="number" name="price" placeholder="Price" step="0.01" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <button type="submit" class="px-3 py-2 bg-slate-900 text-white text-sm rounded-lg">Add Slab</button>
        </form>
    </div>
</div>
@endsection
