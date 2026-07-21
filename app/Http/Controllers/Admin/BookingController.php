<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Mail\ProviderJobAssignedMail;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\PricingSlab;
use App\Models\Service;
use App\Models\ServiceProvider;
use App\Models\Zone;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $query = Booking::with(['service', 'provider', 'customer']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('booking_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date')) {
            $query->whereDate('scheduled_date', $request->date);
        }

        $bookings = $query->latest()->paginate(15)->withQueryString();

        return view('admin.bookings.index', compact('bookings'));
    }

    public function create()
    {
        $services = Service::where('is_active', true)->with('pricingSlabs')->get();
        $providers = ServiceProvider::where('is_active', true)->get();
        $zones = Zone::where('is_active', true)->get();

        return view('admin.bookings.create', compact('services', 'providers', 'zones'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:15',
            'customer_address' => 'required|string',
            'pincode' => 'nullable|string|max:10',
            'service_id' => 'required|exists:services,id',
            'pricing_slab_id' => 'nullable|exists:pricing_slabs,id',
            'provider_id' => 'nullable|exists:service_providers,id',
            'zone_id' => 'nullable|exists:zones,id',
            'tank_type' => 'nullable|string|max:100',
            'tank_size' => 'nullable|string|max:100',
            'special_notes' => 'nullable|string',
            'scheduled_date' => 'required|date|after_or_equal:today',
            'scheduled_time' => 'nullable',
            'amount' => 'required|numeric|min:0',
        ]);

        $customer = Customer::firstOrCreate(
            ['phone' => $validated['customer_phone']],
            [
                'name' => $validated['customer_name'],
                'address' => $validated['customer_address'],
                'pincode' => $validated['pincode'] ?? null,
            ]
        );

        $slab = isset($validated['pricing_slab_id'])
            ? PricingSlab::find($validated['pricing_slab_id'])
            : null;

        $booking = Booking::create([
            ...$validated,
            'booking_number' => Booking::generateBookingNumber(),
            'customer_id' => $customer->id,
            'amount' => $validated['amount'] ?: ($slab?->effectivePrice() ?? 0),
            'status' => $validated['provider_id'] ? BookingStatus::Assigned : BookingStatus::Pending,
            'assigned_at' => $validated['provider_id'] ? now() : null,
            'created_by' => auth()->id(),
        ]);

        AuditService::log('booking_created', $booking, null, $booking->toArray());

        if ($booking->provider_id) {
            $this->notifyProviderJobAssigned($booking);
        }

        return redirect()->route('admin.bookings.show', $booking)
            ->with('success', 'Booking created successfully.');
    }

    public function show(Booking $booking)
    {
        $booking->load(['service', 'pricingSlab', 'provider', 'customer', 'zone', 'photos', 'feedback', 'creator']);
        $providers = ServiceProvider::where('is_active', true)->get();

        return view('admin.bookings.show', compact('booking', 'providers'));
    }

    public function edit(Booking $booking)
    {
        $services = Service::where('is_active', true)->with('pricingSlabs')->get();
        $providers = ServiceProvider::where('is_active', true)->get();
        $zones = Zone::where('is_active', true)->get();

        return view('admin.bookings.edit', compact('booking', 'services', 'providers', 'zones'));
    }

    public function update(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:15',
            'customer_address' => 'required|string',
            'pincode' => 'nullable|string|max:10',
            'service_id' => 'required|exists:services,id',
            'pricing_slab_id' => 'nullable|exists:pricing_slabs,id',
            'provider_id' => 'nullable|exists:service_providers,id',
            'zone_id' => 'nullable|exists:zones,id',
            'tank_type' => 'nullable|string|max:100',
            'tank_size' => 'nullable|string|max:100',
            'special_notes' => 'nullable|string',
            'scheduled_date' => 'required|date',
            'scheduled_time' => 'nullable',
            'amount' => 'required|numeric|min:0',
            'status' => 'required|in:pending,assigned,in_progress,completed,cancelled',
            'payment_status' => 'required|in:pending,paid,refunded',
        ]);

        $old = $booking->toArray();
        $booking->update($validated);

        AuditService::log('booking_updated', $booking, $old, $booking->fresh()->toArray());

        return redirect()->route('admin.bookings.show', $booking)
            ->with('success', 'Booking updated successfully.');
    }

    public function assign(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'provider_id' => 'required|exists:service_providers,id',
        ]);

        $booking->update([
            'provider_id' => $validated['provider_id'],
            'status' => BookingStatus::Assigned,
            'assigned_at' => now(),
            'provider_accepted_at' => null,
            'provider_rejected_at' => null,
            'rejection_reason' => null,
        ]);

        AuditService::log('booking_assigned', $booking);

        $emailSent = $this->notifyProviderJobAssigned($booking->fresh(['service', 'provider']));

        return back()->with(
            $emailSent ? 'success' : 'warning',
            $emailSent
                ? 'Provider assigned successfully. Notification email sent.'
                : 'Provider assigned successfully, but notification email could not be sent.'
        );
    }

    protected function notifyProviderJobAssigned(Booking $booking): bool
    {
        $booking->loadMissing(['service', 'provider']);

        if (! $booking->provider?->email) {
            return false;
        }

        try {
            Mail::to($booking->provider->email)->send(
                new ProviderJobAssignedMail($booking->provider, $booking)
            );

            return true;
        } catch (Throwable $e) {
            Log::error('Provider job assigned email failed', [
                'booking_id' => $booking->id,
                'provider_id' => $booking->provider_id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function cancel(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'cancellation_reason' => 'required|string|max:500',
        ]);

        $booking->update([
            'status' => BookingStatus::Cancelled,
            'cancelled_at' => now(),
            'cancellation_reason' => $validated['cancellation_reason'],
        ]);

        AuditService::log('booking_cancelled', $booking);

        return back()->with('success', 'Booking cancelled.');
    }

    public function destroy(Booking $booking)
    {
        AuditService::log('booking_deleted', $booking, $booking->toArray());
        $booking->delete();

        return redirect()->route('admin.bookings.index')
            ->with('success', 'Booking deleted.');
    }
}
