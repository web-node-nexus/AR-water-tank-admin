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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;
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
        $validated = $request->validate($this->bookingRules());

        $booking = $this->createBooking($validated);

        return redirect()->route('admin.bookings.show', $booking)
            ->with('success', 'Booking created successfully.');
    }

    public function importForm()
    {
        $services = Service::where('is_active', true)->orderBy('name')->get(['id', 'name', 'base_price']);
        $providers = ServiceProvider::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $zones = Zone::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('admin.bookings.import', compact('services', 'providers', 'zones'));
    }

    public function downloadTemplate(): StreamedResponse
    {
        $headers = [
            'customer_name',
            'customer_phone',
            'customer_address',
            'latitude',
            'longitude',
            'pincode',
            'zone_id',
            'service_id',
            'tank_type',
            'tank_size',
            'amount',
            'scheduled_date',
            'scheduled_time',
            'provider_id',
            'special_notes',
        ];

        $firstService = Service::where('is_active', true)->first();
        $firstProvider = ServiceProvider::where('is_active', true)->first();
        $firstZone = Zone::where('is_active', true)->first();

        $sample = [
            'Rahul Sharma',
            '9876543210',
            'House 42, Rajeev Nagar, North West Delhi',
            '28.7041000',
            '77.1025000',
            '110085',
            $firstZone?->id ?? '',
            $firstService?->id ?? '',
            'Overhead',
            '1000L',
            $firstService?->base_price ?? 999,
            date('Y-m-d', strtotime('+1 day')),
            '10:00',
            $firstProvider?->id ?? '',
            'Call before arrival',
        ];

        return response()->streamDownload(function () use ($headers, $sample) {
            $out = fopen('php://output', 'w');
            // UTF-8 BOM so Excel opens Hindi/special chars correctly
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $headers);
            fputcsv($out, $sample);
            fclose($out);
        }, 'booking-import-template.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv,txt|max:5120',
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension());

        try {
            $matrix = $this->readImportRows($file->getRealPath(), $extension);
        } catch (Throwable $e) {
            Log::error('Booking import parse failed', ['error' => $e->getMessage()]);

            return back()->with('error', $e->getMessage());
        }

        if (count($matrix) < 2) {
            return back()->with('error', 'File is empty. Add at least one booking row below the header.');
        }

        $headerRow = array_shift($matrix);
        $headers = [];
        foreach ($headerRow as $col => $value) {
            $key = strtolower(trim((string) $value));
            // strip BOM from first header if present
            $key = preg_replace('/^\x{FEFF}/u', '', $key) ?? $key;
            if ($key !== '') {
                $headers[$col] = $key;
            }
        }

        $required = ['customer_name', 'customer_phone', 'customer_address', 'service_id', 'amount', 'scheduled_date'];
        foreach ($required as $field) {
            if (! in_array($field, $headers, true)) {
                return back()->with('error', "Missing required column: {$field}");
            }
        }

        $created = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($matrix as $index => $row) {
                $line = $index + 2;
                $data = [];
                foreach ($headers as $col => $field) {
                    $data[$field] = isset($row[$col]) ? trim((string) $row[$col]) : null;
                    if ($data[$field] === '') {
                        $data[$field] = null;
                    }
                }

                if ($this->rowIsEmpty($data)) {
                    continue;
                }

                $data['scheduled_date'] = $this->normalizeExcelDate($data['scheduled_date'] ?? null);
                $data['scheduled_time'] = $this->normalizeExcelTime($data['scheduled_time'] ?? null);

                if (isset($data['latitude'])) {
                    $data['latitude'] = is_numeric($data['latitude']) ? (float) $data['latitude'] : null;
                }
                if (isset($data['longitude'])) {
                    $data['longitude'] = is_numeric($data['longitude']) ? (float) $data['longitude'] : null;
                }
                if (isset($data['amount'])) {
                    $data['amount'] = is_numeric($data['amount']) ? (float) $data['amount'] : $data['amount'];
                }
                if (isset($data['service_id']) && ! is_numeric($data['service_id'])) {
                    $service = Service::where('name', $data['service_id'])->first();
                    $data['service_id'] = $service?->id;
                }
                if (isset($data['provider_id']) && $data['provider_id'] !== null && ! is_numeric($data['provider_id'])) {
                    $lookup = $data['provider_id'];
                    $provider = ServiceProvider::where(function ($q) use ($lookup) {
                        $q->where('name', $lookup)->orWhere('phone', $lookup);
                    })->first();
                    $data['provider_id'] = $provider?->id;
                }

                $validator = Validator::make($data, $this->bookingRules(forImport: true));

                if ($validator->fails()) {
                    $errors[] = "Row {$line}: ".$validator->errors()->first();
                    continue;
                }

                $this->createBooking($validator->validated());
                $created++;
            }

            if ($created === 0) {
                DB::rollBack();

                return back()->with('error', $errors[0] ?? 'No valid booking rows found in the file.');
            }

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Booking Excel import failed', ['error' => $e->getMessage()]);

            return back()->with('error', 'Import failed: '.$e->getMessage());
        }

        $message = "{$created} booking(s) created successfully.";
        if ($errors) {
            $message .= ' Some rows were skipped: '.implode(' | ', array_slice($errors, 0, 5));
            if (count($errors) > 5) {
                $message .= ' (+'.(count($errors) - 5).' more)';
            }

            return redirect()->route('admin.bookings.index')->with('warning', $message);
        }

        return redirect()->route('admin.bookings.index')->with('success', $message);
    }

    /**
     * @return list<list<mixed>>
     */
    protected function readImportRows(string $path, string $extension): array
    {
        if (in_array($extension, ['csv', 'txt'], true)) {
            return $this->readCsvRows($path);
        }

        if (! class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
            throw new \RuntimeException(
                'Excel (.xlsx) support is not installed on server. Please upload the CSV template, or run composer install on the server.'
            );
        }

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
        $sheetRows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

        return array_values(array_map(
            fn ($row) => array_values($row ?? []),
            $sheetRows
        ));
    }

    /**
     * @return list<list<string|null>>
     */
    protected function readCsvRows(string $path): array
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new \RuntimeException('Could not read uploaded file.');
        }

        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            $rows[] = $row;
        }
        fclose($handle);

        return $rows;
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
            ...$this->bookingRules(forUpdate: true),
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

    protected function bookingRules(bool $forUpdate = false, bool $forImport = false): array
    {
        return [
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:15',
            'customer_address' => 'required|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'pincode' => 'nullable|string|max:10',
            'service_id' => 'required|exists:services,id',
            'pricing_slab_id' => 'nullable|exists:pricing_slabs,id',
            'provider_id' => 'nullable|exists:service_providers,id',
            'zone_id' => 'nullable|exists:zones,id',
            'tank_type' => 'nullable|string|max:100',
            'tank_size' => 'nullable|string|max:100',
            'special_notes' => 'nullable|string',
            'scheduled_date' => $forUpdate || $forImport
                ? 'required|date'
                : 'required|date|after_or_equal:today',
            'scheduled_time' => 'nullable',
            'amount' => 'required|numeric|min:0',
        ];
    }

    protected function createBooking(array $validated): Booking
    {
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
            'status' => ! empty($validated['provider_id']) ? BookingStatus::Assigned : BookingStatus::Pending,
            'assigned_at' => ! empty($validated['provider_id']) ? now() : null,
            'created_by' => auth()->id(),
        ]);

        AuditService::log('booking_created', $booking, null, $booking->toArray());

        if ($booking->provider_id) {
            $this->notifyProviderJobAssigned($booking);
        }

        return $booking;
    }

    protected function rowIsEmpty(array $data): bool
    {
        foreach (['customer_name', 'customer_phone', 'customer_address', 'service_id', 'amount', 'scheduled_date'] as $field) {
            if (! empty($data[$field])) {
                return false;
            }
        }

        return true;
    }

    protected function normalizeExcelDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            $serial = (float) $value;
            if (class_exists(\PhpOffice\PhpSpreadsheet\Shared\Date::class)) {
                try {
                    return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($serial)->format('Y-m-d');
                } catch (Throwable) {
                    // fall through
                }
            }

            // Excel serial date → unix (days since 1899-12-30)
            if ($serial > 20000) {
                return gmdate('Y-m-d', (int) (($serial - 25569) * 86400));
            }

            return (string) $value;
        }

        $timestamp = strtotime((string) $value);

        return $timestamp ? date('Y-m-d', $timestamp) : (string) $value;
    }

    protected function normalizeExcelTime(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value) && (float) $value < 1) {
            $fraction = (float) $value;
            if (class_exists(\PhpOffice\PhpSpreadsheet\Shared\Date::class)) {
                try {
                    return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($fraction)->format('H:i');
                } catch (Throwable) {
                    // fall through
                }
            }

            $seconds = (int) round($fraction * 86400);

            return gmdate('H:i', $seconds);
        }

        $raw = trim((string) $value);
        if (preg_match('/^\d{1,2}:\d{2}/', $raw)) {
            return substr($raw, 0, 5);
        }

        $timestamp = strtotime($raw);

        return $timestamp ? date('H:i', $timestamp) : $raw;
    }
}
