<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = $this->filteredCustomers($request)->latest()->paginate(15)->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    public function export(Request $request): StreamedResponse
    {
        $filename = 'customers-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($request) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [
                'id',
                'name',
                'phone',
                'email',
                'address',
                'pincode',
                'bookings',
                'total_spent',
                'notes',
                'created_at',
            ]);

            $this->filteredCustomers($request)
                ->latest()
                ->chunk(200, function ($customers) use ($out) {
                    foreach ($customers as $customer) {
                        fputcsv($out, [
                            $customer->id,
                            $customer->name,
                            $customer->phone,
                            $customer->email,
                            $customer->address,
                            $customer->pincode,
                            $customer->bookings_count,
                            $customer->total_spent,
                            $customer->notes,
                            optional($customer->created_at)->format('Y-m-d H:i'),
                        ]);
                    }
                });

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function show(Customer $customer)
    {
        $customer->load(['bookings.service', 'bookings.provider', 'feedback']);

        return view('admin.customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        return view('admin.customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:15|unique:customers,phone,'.$customer->id,
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'pincode' => 'nullable|string|max:10',
            'notes' => 'nullable|string',
        ]);

        $customer->update($validated);

        return redirect()->route('admin.customers.show', $customer)
            ->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        $name = $customer->name;
        AuditService::log('customer_deleted', $customer, $customer->toArray());
        $customer->delete();

        return redirect()->route('admin.customers.index')
            ->with('success', $name.' has been deleted.');
    }

    protected function filteredCustomers(Request $request)
    {
        $query = Customer::withCount('bookings');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query;
    }
}
