@extends('layouts.admin')

@section('title', 'Notifications')
@section('page-title', 'Notifications')
@section('page-subtitle', 'Sent notifications to providers')

@section('content')
<div class="space-y-4">
    <div class="flex justify-end">
        <a href="{{ route('admin.notifications.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-cyan-600 to-blue-600 text-white text-sm font-semibold rounded-xl hover:from-cyan-700 hover:to-blue-700 shadow-lg shadow-cyan-500/25">
            + Send Notification
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="text-left px-6 py-3 font-medium">Title</th>
                        <th class="text-left px-6 py-3 font-medium">Target</th>
                        <th class="text-left px-6 py-3 font-medium">Sent By</th>
                        <th class="text-left px-6 py-3 font-medium">Sent At</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($notifications as $notification)
                    <tr class="hover:bg-slate-50/50">
                        <td class="px-6 py-3">
                            <p class="font-medium text-slate-900">{{ $notification->title }}</p>
                            <p class="text-xs text-slate-500 mt-1 max-w-md truncate">{{ $notification->body }}</p>
                        </td>
                        <td class="px-6 py-3">
                            @if($notification->target_type === 'all')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-cyan-100 text-cyan-800">All Providers</span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800">
                                {{ count($notification->target_provider_ids ?? []) }} provider(s)
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-slate-600">{{ $notification->sender?->name ?? '—' }}</td>
                        <td class="px-6 py-3 text-slate-600">{{ $notification->sent_at?->format('d M Y, h:i A') ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-6 py-12 text-center text-slate-500">No notifications sent yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($notifications->hasPages())
        <div class="px-6 py-4 border-t border-slate-100">{{ $notifications->links() }}</div>
        @endif
    </div>
</div>
@endsection
