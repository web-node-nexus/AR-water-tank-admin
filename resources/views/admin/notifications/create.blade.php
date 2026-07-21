@extends('layouts.admin')

@section('title', 'Send Notification')
@section('page-title', 'Send Notification')
@section('page-subtitle', 'Send a push notification to providers')

@section('content')
<div class="max-w-2xl">
    <form method="POST" action="{{ route('admin.notifications.store') }}" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-6">
        @csrf
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Title *</label>
            <input type="text" name="title" value="{{ old('title') }}" required placeholder="Notification title" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 outline-none">
            @error('title')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Body *</label>
            <textarea name="body" rows="4" required placeholder="Notification message..." class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 outline-none">{{ old('body') }}</textarea>
            @error('body')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-3">Target *</label>
            <div class="flex gap-4 mb-4">
                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="radio" name="target_type" value="all" @checked(old('target_type', 'all') === 'all') class="text-cyan-600 focus:ring-cyan-500" onchange="toggleProviders(false)">
                    All Providers
                </label>
                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="radio" name="target_type" value="specific" @checked(old('target_type') === 'specific') class="text-cyan-600 focus:ring-cyan-500" onchange="toggleProviders(true)">
                    Specific Providers
                </label>
            </div>
            @error('target_type')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror

            <div id="provider-list" class="hidden border border-slate-200 rounded-xl p-4 max-h-64 overflow-y-auto space-y-2">
                @foreach($providers as $provider)
                <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-slate-50 cursor-pointer">
                    <input type="checkbox" name="target_provider_ids[]" value="{{ $provider->id }}"
                        @checked(in_array($provider->id, old('target_provider_ids', [])))
                        class="rounded text-cyan-600 focus:ring-cyan-500">
                    <span class="text-sm text-slate-700">{{ $provider->name }} <span class="text-slate-400">({{ $provider->phone }})</span></span>
                </label>
                @endforeach
            </div>
            @error('target_provider_ids')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div class="flex gap-3 pt-2">
            <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-cyan-600 to-blue-600 text-white font-semibold rounded-xl hover:from-cyan-700 hover:to-blue-700 shadow-lg shadow-cyan-500/25">Send Notification</button>
            <a href="{{ route('admin.notifications.index') }}" class="px-6 py-2.5 border border-slate-300 text-slate-700 font-medium rounded-xl hover:bg-slate-50">Cancel</a>
        </div>
    </form>
</div>

<script>
function toggleProviders(show) {
    const list = document.getElementById('provider-list');
    if (show) {
        list.classList.remove('hidden');
    } else {
        list.classList.add('hidden');
        list.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
    }
}
document.addEventListener('DOMContentLoaded', function() {
    const specific = document.querySelector('input[name="target_type"][value="specific"]');
    if (specific && specific.checked) toggleProviders(true);
});
</script>
@endsection
