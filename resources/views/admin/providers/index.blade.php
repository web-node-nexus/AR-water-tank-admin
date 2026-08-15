@extends('layouts.admin')

@section('title', 'Service Providers')
@section('page-title', 'Service Providers')
@section('page-subtitle', 'Create accounts, enable/disable providers, assign jobs from Bookings')

@section('content')
<div class="space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <form method="GET" class="flex flex-wrap gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, phone, email..."
                class="rounded-xl border border-slate-300 px-4 py-2 text-sm w-64 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 outline-none">
            <select name="status" class="rounded-xl border border-slate-300 px-4 py-2 text-sm focus:border-cyan-500 outline-none">
                <option value="">All Status</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Disabled</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-slate-900 text-white text-sm font-medium rounded-xl hover:bg-slate-800">Filter</button>
        </form>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.providers.export', request()->query()) }}" class="inline-flex items-center gap-2 px-4 py-2 border border-slate-300 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-50">
                Export CSV
            </a>
            <a href="{{ route('admin.providers.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-cyan-600 to-blue-600 text-white text-sm font-semibold rounded-xl hover:from-cyan-700 hover:to-blue-700 shadow-lg shadow-cyan-500/25">
                + Add Provider
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="text-left px-6 py-3 font-medium">ID</th>
                        <th class="text-left px-6 py-3 font-medium">Name</th>
                        <th class="text-left px-6 py-3 font-medium">Email / Phone</th>
                        <th class="text-left px-6 py-3 font-medium">Zone</th>
                        <th class="text-left px-6 py-3 font-medium">Jobs</th>
                        <th class="text-left px-6 py-3 font-medium">Rating</th>
                        <th class="text-left px-6 py-3 font-medium">Status</th>
                        <th class="text-left px-6 py-3 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($providers as $provider)
                    <tr class="hover:bg-slate-50/50 {{ !$provider->is_active ? 'opacity-70' : '' }}">
                        <td class="px-6 py-3 font-medium text-cyan-600">{{ $provider->id }}</td>
                        <td class="px-6 py-3">
                            <a href="{{ route('admin.providers.show', $provider) }}" class="font-medium text-cyan-600 hover:underline">{{ $provider->name }}</a>
                        </td>
                        <td class="px-6 py-3">
                            <p class="text-slate-900">{{ $provider->email }}</p>
                            <p class="text-xs text-slate-500">{{ $provider->phone }}</p>
                        </td>
                        <td class="px-6 py-3 text-slate-600">{{ $provider->zone?->name ?? '—' }}</td>
                        <td class="px-6 py-3 font-medium text-slate-900">{{ $provider->bookings_count }}</td>
                        <td class="px-6 py-3 text-amber-600 font-medium">{{ number_format($provider->rating_avg, 1) }} ★</td>
                        <td class="px-6 py-3">
                            <form method="POST" action="{{ route('admin.providers.toggle-status', $provider) }}">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold transition-colors {{ $provider->is_active ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-red-100 text-red-800 hover:bg-red-200' }}">
                                    {{ $provider->is_active ? '● Enabled' : '○ Disabled' }}
                                </button>
                            </form>
                        </td>
                        <td class="px-6 py-3">
                            <div class="flex items-center gap-3">
                                <a href="{{ route('admin.providers.show', $provider) }}" class="text-cyan-600 hover:underline text-xs font-medium">View</a>
                                <a href="{{ route('admin.providers.edit', $provider) }}" class="text-slate-600 hover:underline text-xs font-medium">Edit</a>
                                <form method="POST" action="{{ route('admin.providers.destroy', $provider) }}" onsubmit="return confirm('Delete provider {{ $provider->name }}? Bookings will stay, but this provider will be removed.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline text-xs font-medium">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="px-6 py-12 text-center text-slate-500">No providers found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($providers->hasPages())
        <div class="px-6 py-4 border-t border-slate-100">{{ $providers->links() }}</div>
        @endif
    </div>
</div>
@endsection
