@extends('layouts.admin')

@section('title', 'Import Bookings')
@section('page-title', 'Bulk Import Bookings')
@section('page-subtitle', 'Upload an Excel file to create multiple bookings at once')

@section('content')
<div class="max-w-3xl space-y-6">
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-5">
        <div>
            <h2 class="text-base font-semibold text-slate-900">Upload Excel / CSV</h2>
            <p class="text-sm text-slate-500 mt-1">Template download karo (CSV — Excel me open hoga), rows bharo, phir upload karo. Har row = ek booking.</p>
        </div>

        <a href="{{ route('admin.bookings.import.template') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 border border-slate-300 text-slate-700 text-sm font-medium rounded-xl hover:bg-slate-50">
            Download Template (CSV / Excel)
        </a>

        <form method="POST" action="{{ route('admin.bookings.import.store') }}" enctype="multipart/form-data" class="space-y-4 pt-2">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">File (.csv / .xlsx) *</label>
                <input type="file" name="file" accept=".xlsx,.xls,.csv,text/csv" required
                       class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-cyan-50 file:text-cyan-700 file:font-medium">
                @error('file')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="flex gap-3">
                <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-cyan-600 to-blue-600 text-white font-semibold rounded-xl hover:from-cyan-700 hover:to-blue-700 shadow-lg shadow-cyan-500/25">
                    Import Bookings
                </button>
                <a href="{{ route('admin.bookings.index') }}" class="px-6 py-2.5 border border-slate-300 text-slate-700 font-medium rounded-xl hover:bg-slate-50">Cancel</a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
        <h3 class="text-sm font-semibold text-slate-900 mb-3">Required columns</h3>
        <p class="text-xs text-slate-500 mb-4">Headers must match exactly (see template). Optional: latitude, longitude, pincode, zone_id, tank_type, tank_size, scheduled_time, provider_id, special_notes.</p>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="text-left px-3 py-2 font-medium">ID</th>
                        <th class="text-left px-3 py-2 font-medium">Name</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr class="bg-slate-50/80"><td colspan="2" class="px-3 py-2 text-xs font-semibold text-slate-600 uppercase tracking-wide">Services (service_id)</td></tr>
                    @foreach($services as $service)
                    <tr>
                        <td class="px-3 py-2 font-medium text-cyan-600">{{ $service->id }}</td>
                        <td class="px-3 py-2">{{ $service->name }} <span class="text-slate-400">— ₹{{ number_format($service->base_price) }}</span></td>
                    </tr>
                    @endforeach
                    <tr class="bg-slate-50/80"><td colspan="2" class="px-3 py-2 text-xs font-semibold text-slate-600 uppercase tracking-wide">Providers (provider_id)</td></tr>
                    @forelse($providers as $provider)
                    <tr>
                        <td class="px-3 py-2 font-medium text-cyan-600">{{ $provider->id }}</td>
                        <td class="px-3 py-2">{{ $provider->name }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="2" class="px-3 py-2 text-slate-400">No active providers</td></tr>
                    @endforelse
                    <tr class="bg-slate-50/80"><td colspan="2" class="px-3 py-2 text-xs font-semibold text-slate-600 uppercase tracking-wide">Zones (zone_id)</td></tr>
                    @forelse($zones as $zone)
                    <tr>
                        <td class="px-3 py-2 font-medium text-cyan-600">{{ $zone->id }}</td>
                        <td class="px-3 py-2">{{ $zone->name }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="2" class="px-3 py-2 text-slate-400">No active zones</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
