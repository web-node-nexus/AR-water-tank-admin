@extends('layouts.admin')

@section('title', 'Create Payout')
@section('page-title', 'Create Payout')
@section('page-subtitle', 'Record a new provider payout')

@section('content')
<div class="max-w-2xl">
    <form method="POST" action="{{ route('admin.payouts.store') }}" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-6">
        @csrf
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Provider *</label>
            <select name="provider_id" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 outline-none">
                <option value="">Select Provider</option>
                @foreach($providers as $provider)
                    <option value="{{ $provider->id }}" @selected(old('provider_id', request('provider_id')) == $provider->id)>{{ $provider->name }} ({{ $provider->phone }})</option>
                @endforeach
            </select>
            @error('provider_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Amount (₹) *</label>
            <input type="number" name="amount" value="{{ old('amount') }}" step="0.01" min="0" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 outline-none">
            @error('amount')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Period Start *</label>
                <input type="date" name="period_start" value="{{ old('period_start') }}" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 outline-none">
                @error('period_start')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Period End *</label>
                <input type="date" name="period_end" value="{{ old('period_end') }}" required class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 outline-none">
                @error('period_end')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
            <textarea name="notes" rows="3" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 outline-none">{{ old('notes') }}</textarea>
            @error('notes')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div class="flex gap-3 pt-2">
            <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-cyan-600 to-blue-600 text-white font-semibold rounded-xl hover:from-cyan-700 hover:to-blue-700 shadow-lg shadow-cyan-500/25">Create Payout</button>
            <a href="{{ route('admin.payouts.index') }}" class="px-6 py-2.5 border border-slate-300 text-slate-700 font-medium rounded-xl hover:bg-slate-50">Cancel</a>
        </div>
    </form>
</div>
@endsection
