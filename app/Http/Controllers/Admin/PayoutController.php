<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payout;
use App\Models\ServiceProvider;
use App\Services\AuditService;
use Illuminate\Http\Request;

class PayoutController extends Controller
{
    public function index(Request $request)
    {
        $query = Payout::with('provider');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $payouts = $query->latest()->paginate(15)->withQueryString();

        return view('admin.payouts.index', compact('payouts'));
    }

    public function create()
    {
        $providers = ServiceProvider::where('is_active', true)->get();

        return view('admin.payouts.create', compact('providers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'provider_id' => 'required|exists:service_providers,id',
            'amount' => 'required|numeric|min:0',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'notes' => 'nullable|string',
        ]);

        $payout = Payout::create([
            ...$validated,
            'status' => 'pending',
        ]);

        AuditService::log('payout_created', $payout);

        return redirect()->route('admin.payouts.index')
            ->with('success', 'Payout record created.');
    }

    public function markPaid(Payout $payout)
    {
        $payout->update([
            'status' => 'paid',
            'paid_at' => now(),
            'processed_by' => auth()->id(),
        ]);

        AuditService::log('payout_paid', $payout);

        return back()->with('success', 'Payout marked as paid.');
    }
}
