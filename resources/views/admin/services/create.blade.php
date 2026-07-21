@extends('layouts.admin')
@section('title', 'Add Service')
@section('page-title', 'Add Service')
@section('content')
<div class="max-w-2xl">
    <form method="POST" action="{{ route('admin.services.store') }}" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
        @csrf
        <div><label class="block text-sm font-medium text-slate-700 mb-1">Name *</label><input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 outline-none"></div>
        <div><label class="block text-sm font-medium text-slate-700 mb-1">Category *</label>
            <select name="category" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 outline-none">
                @foreach(['Overhead Water Tank','Underground Water Tank','Residential','Commercial','Industrial','Cement Tank'] as $cat)
                    <option value="{{ $cat }}" @selected(old('category') === $cat)>{{ $cat }}</option>
                @endforeach
            </select>
        </div>
        <div><label class="block text-sm font-medium text-slate-700 mb-1">Description</label><textarea name="description" rows="2" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 outline-none">{{ old('description') }}</textarea></div>
        <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-slate-700 mb-1">Base Price (₹) *</label><input type="number" name="base_price" value="{{ old('base_price') }}" step="0.01" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 outline-none"></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1">Sort Order</label><input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 outline-none"></div>
        </div>
        <div class="flex gap-4">
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" checked class="rounded text-cyan-600"> Active</label>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_featured" value="1" class="rounded text-cyan-600"> Featured</label>
        </div>
        <div class="flex gap-3 pt-2">
            <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-cyan-600 to-blue-600 text-white font-semibold rounded-xl">Create Service</button>
            <a href="{{ route('admin.services.index') }}" class="px-6 py-2.5 border border-slate-300 text-slate-700 rounded-xl hover:bg-slate-50">Cancel</a>
        </div>
    </form>
</div>
@endsection
