<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PricingSlab;
use App\Models\Service;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::withCount('pricingSlabs')->orderBy('sort_order')->get();

        return view('admin.services.index', compact('services'));
    }

    public function create()
    {
        return view('admin.services.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer|min:0',
            'slabs' => 'nullable|array',
            'slabs.*.name' => 'required_with:slabs|string',
            'slabs.*.min_capacity' => 'required_with:slabs|integer|min:0',
            'slabs.*.max_capacity' => 'nullable|integer',
            'slabs.*.price' => 'required_with:slabs|numeric|min:0',
            'slabs.*.sale_price' => 'nullable|numeric|min:0',
        ]);

        $service = Service::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'category' => $validated['category'],
            'description' => $validated['description'] ?? null,
            'base_price' => $validated['base_price'],
            'is_active' => $request->boolean('is_active', true),
            'is_featured' => $request->boolean('is_featured'),
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        if (! empty($validated['slabs'])) {
            foreach ($validated['slabs'] as $slab) {
                $service->pricingSlabs()->create($slab);
            }
        }

        AuditService::log('service_created', $service);

        return redirect()->route('admin.services.index')
            ->with('success', 'Service created successfully.');
    }

    public function edit(Service $service)
    {
        $service->load('pricingSlabs');

        return view('admin.services.edit', compact('service'));
    }

    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $service->update([
            ...$validated,
            'slug' => Str::slug($validated['name']),
            'is_active' => $request->boolean('is_active', true),
            'is_featured' => $request->boolean('is_featured'),
        ]);

        AuditService::log('service_updated', $service);

        return redirect()->route('admin.services.index')
            ->with('success', 'Service updated successfully.');
    }

    public function destroy(Service $service)
    {
        if ($service->bookings()->exists()) {
            return back()->with('error', 'Cannot delete service with existing bookings.');
        }

        AuditService::log('service_deleted', $service);
        $service->pricingSlabs()->delete();
        $service->delete();

        return redirect()->route('admin.services.index')
            ->with('success', 'Service deleted.');
    }

    public function storeSlab(Request $request, Service $service)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'min_capacity' => 'required|integer|min:0',
            'max_capacity' => 'nullable|integer',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
        ]);

        $service->pricingSlabs()->create($validated);

        return back()->with('success', 'Pricing slab added.');
    }

    public function destroySlab(PricingSlab $slab)
    {
        $slab->delete();

        return back()->with('success', 'Pricing slab removed.');
    }
}
