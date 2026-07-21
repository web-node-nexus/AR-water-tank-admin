<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Zone;
use Illuminate\Http\Request;

class ZoneController extends Controller
{
    public function index()
    {
        $zones = Zone::withCount('providers')->get();

        return view('admin.zones.index', compact('zones'));
    }

    public function create()
    {
        return view('admin.zones.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:zones,code',
            'name' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'pincodes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $pincodes = $validated['pincodes']
            ? array_map('trim', explode(',', $validated['pincodes']))
            : [];

        Zone::create([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'city' => $validated['city'],
            'pincodes' => $pincodes,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.zones.index')
            ->with('success', 'Zone created successfully.');
    }

    public function edit(Zone $zone)
    {
        return view('admin.zones.edit', compact('zone'));
    }

    public function update(Request $request, Zone $zone)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:zones,code,'.$zone->id,
            'name' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'pincodes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $pincodes = $validated['pincodes']
            ? array_map('trim', explode(',', $validated['pincodes']))
            : [];

        $zone->update([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'city' => $validated['city'],
            'pincodes' => $pincodes,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.zones.index')
            ->with('success', 'Zone updated successfully.');
    }

    public function destroy(Zone $zone)
    {
        if ($zone->providers()->exists()) {
            return back()->with('error', 'Cannot delete zone with assigned providers.');
        }

        $zone->delete();

        return redirect()->route('admin.zones.index')
            ->with('success', 'Zone deleted.');
    }
}
