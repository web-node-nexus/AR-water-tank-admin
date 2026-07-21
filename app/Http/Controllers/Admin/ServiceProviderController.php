<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ProviderCredentialsMail;
use App\Models\ServiceProvider;
use App\Models\Zone;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Throwable;

class ServiceProviderController extends Controller
{
    protected function sendCredentialsEmail(ServiceProvider $provider, string $plainPassword, bool $isReset = false): bool
    {
        try {
            Mail::to($provider->email)->send(new ProviderCredentialsMail($provider, $plainPassword, isReset: $isReset));

            return true;
        } catch (Throwable $e) {
            Log::error('Provider credentials email failed', [
                'provider_id' => $provider->id,
                'email' => $provider->email,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
    public function index(Request $request)
    {
        $query = ServiceProvider::with('zone')->withCount('bookings');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $providers = $query->latest()->paginate(15)->withQueryString();

        return view('admin.providers.index', compact('providers'));
    }

    public function create()
    {
        $zones = Zone::where('is_active', true)->get();

        return view('admin.providers.create', compact('zones'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:15|unique:service_providers,phone',
            'email' => 'required|email|max:255|unique:service_providers,email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'zone_id' => 'nullable|exists:zones,id',
            'service_area' => 'nullable|string|max:255',
            'availability_status' => 'required|in:available,busy,unavailable',
            'is_active' => 'boolean',
        ]);

        $plainPassword = $validated['password'];

        $provider = ServiceProvider::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'password' => $plainPassword,
            'zone_id' => $validated['zone_id'] ?? null,
            'service_area' => $validated['service_area'] ?? null,
            'availability_status' => $validated['availability_status'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        $emailSent = $this->sendCredentialsEmail($provider, $plainPassword);

        AuditService::log('provider_created', $provider);

        if ($emailSent) {
            return redirect()->route('admin.providers.index')
                ->with('success', 'Service provider added. Login credentials sent to '.$provider->email);
        }

        return redirect()->route('admin.providers.index')
            ->with('warning', 'Provider added, but email could not be sent. Share these credentials manually — Email: '.$provider->email.' | Password: '.$plainPassword);
    }

    public function show(ServiceProvider $provider)
    {
        $provider->load(['zone', 'bookings.service', 'payouts', 'leaveRequests', 'feedback']);

        return view('admin.providers.show', compact('provider'));
    }

    public function edit(ServiceProvider $provider)
    {
        $zones = Zone::where('is_active', true)->get();

        return view('admin.providers.edit', compact('provider', 'zones'));
    }

    public function update(Request $request, ServiceProvider $provider)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:15|unique:service_providers,phone,'.$provider->id,
            'email' => 'required|email|max:255|unique:service_providers,email,'.$provider->id,
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'zone_id' => 'nullable|exists:zones,id',
            'service_area' => 'nullable|string|max:255',
            'availability_status' => 'required|in:available,busy,unavailable',
            'is_active' => 'boolean',
        ]);

        $data = [
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'zone_id' => $validated['zone_id'] ?? null,
            'service_area' => $validated['service_area'] ?? null,
            'availability_status' => $validated['availability_status'],
            'is_active' => $request->boolean('is_active', true),
        ];

        $plainPassword = null;
        if (! empty($validated['password'])) {
            $plainPassword = $validated['password'];
            $data['password'] = $plainPassword;
        }

        $provider->update($data);

        $emailSent = true;
        if ($plainPassword) {
            $emailSent = $this->sendCredentialsEmail($provider, $plainPassword, isReset: true);
        }

        AuditService::log('provider_updated', $provider);

        $message = match (true) {
            $plainPassword && $emailSent => 'Provider updated. New password sent to '.$provider->email,
            $plainPassword && ! $emailSent => 'Provider updated, but email could not be sent. Use "Resend Login Email" later.',
            default => 'Provider updated successfully.',
        };

        return redirect()->route('admin.providers.show', $provider)->with($emailSent || ! $plainPassword ? 'success' : 'warning', $message);
    }

    public function destroy(ServiceProvider $provider)
    {
        $provider->update(['is_active' => false]);
        $provider->tokens()->delete();

        AuditService::log('provider_deactivated', $provider);

        return redirect()->route('admin.providers.index')
            ->with('success', 'Provider disabled successfully.');
    }

    public function toggleStatus(ServiceProvider $provider)
    {
        $provider->update(['is_active' => ! $provider->is_active]);

        if (! $provider->is_active) {
            $provider->tokens()->delete();
        }

        AuditService::log($provider->is_active ? 'provider_enabled' : 'provider_disabled', $provider);

        return back()->with('success', $provider->is_active
            ? $provider->name.' has been enabled.'
            : $provider->name.' has been disabled.');
    }

    public function resendCredentials(ServiceProvider $provider)
    {
        if (! $provider->email) {
            return back()->with('error', 'Provider has no email address.');
        }

        $plainPassword = Str::password(10);
        $provider->update(['password' => $plainPassword]);

        $emailSent = $this->sendCredentialsEmail($provider, $plainPassword, isReset: true);

        AuditService::log('provider_credentials_resent', $provider);

        return back()->with(
            $emailSent ? 'success' : 'warning',
            $emailSent
                ? 'New login credentials sent to '.$provider->email
                : 'Email could not be sent. Share manually — Email: '.$provider->email.' | New Password: '.$plainPassword
        );
    }
}
