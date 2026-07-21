@extends('layouts.admin')

@section('title', 'Customers')
@section('page-title', 'Customers')
@section('page-subtitle', 'View and manage customer records')

@section('content')
<div class="space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <form method="GET" class="flex flex-wrap gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, phone, email..."
                class="rounded-xl border border-slate-300 px-4 py-2 text-sm w-64 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 outline-none">
            <button type="submit" class="px-4 py-2 bg-slate-900 text-white text-sm font-medium rounded-xl hover:bg-slate-800">Search</button>
        </form>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="text-left px-6 py-3 font-medium">Name</th>
                        <th class="text-left px-6 py-3 font-medium">Phone</th>
                        <th class="text-left px-6 py-3 font-medium">Email</th>
                        <th class="text-left px-6 py-3 font-medium">Bookings</th>
                        <th class="text-left px-6 py-3 font-medium">Total Spent</th>
                        <th class="text-left px-6 py-3 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($customers as $customer)
                    <tr class="hover:bg-slate-50/50">
                        <td class="px-6 py-3">
                            <a href="{{ route('admin.customers.show', $customer) }}" class="font-medium text-cyan-600 hover:underline">{{ $customer->name }}</a>
                        </td>
                        <td class="px-6 py-3 text-slate-600">{{ $customer->phone }}</td>
                        <td class="px-6 py-3 text-slate-600">{{ $customer->email ?? '—' }}</td>
                        <td class="px-6 py-3 font-medium text-slate-900">{{ $customer->bookings_count }}</td>
                        <td class="px-6 py-3 font-medium text-emerald-600">₹{{ number_format($customer->total_spent ?? 0) }}</td>
                        <td class="px-6 py-3">
                            <div class="flex items-center gap-3">
                                <a href="{{ route('admin.customers.show', $customer) }}" class="text-cyan-600 hover:underline text-xs font-medium">View</a>
                                <a href="{{ route('admin.customers.edit', $customer) }}" class="text-slate-600 hover:underline text-xs font-medium">Edit</a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-6 py-12 text-center text-slate-500">No customers found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($customers->hasPages())
        <div class="px-6 py-4 border-t border-slate-100">{{ $customers->links() }}</div>
        @endif
    </div>
</div>
@endsection
