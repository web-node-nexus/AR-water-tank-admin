@props(['status'])
@php
    $styles = [
        'pending' => 'bg-amber-100 text-amber-800',
        'assigned' => 'bg-blue-100 text-blue-800',
        'in_progress' => 'bg-indigo-100 text-indigo-800',
        'completed' => 'bg-emerald-100 text-emerald-800',
        'cancelled' => 'bg-red-100 text-red-800',
        'paid' => 'bg-emerald-100 text-emerald-800',
        'approved' => 'bg-emerald-100 text-emerald-800',
        'rejected' => 'bg-red-100 text-red-800',
        'available' => 'bg-emerald-100 text-emerald-800',
        'busy' => 'bg-amber-100 text-amber-800',
        'unavailable' => 'bg-red-100 text-red-800',
    ];
    $labels = [
        'pending' => 'Pending', 'assigned' => 'Assigned', 'in_progress' => 'In Progress',
        'completed' => 'Completed', 'cancelled' => 'Cancelled', 'paid' => 'Paid',
        'approved' => 'Approved', 'rejected' => 'Rejected',
        'available' => 'Available', 'busy' => 'Busy', 'unavailable' => 'Unavailable',
    ];
    $val = is_object($status) ? $status->value : $status;
@endphp
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $styles[$val] ?? 'bg-slate-100 text-slate-800' }}">
    {{ $labels[$val] ?? ucfirst(str_replace('_', ' ', $val)) }}
</span>
