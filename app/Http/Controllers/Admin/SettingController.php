<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = [
            'company_name' => Setting::get('company_name', 'AR Water Tank Cleaners'),
            'company_phone' => Setting::get('company_phone', '+91 9876543210'),
            'company_email' => Setting::get('company_email', 'info@arwatertankcleaners.in'),
            'company_address' => Setting::get('company_address', 'Plot No S-15,16 Rajeev Nagar, North West Delhi - 110042'),
            'booking_slot_start' => Setting::get('booking_slot_start', '09:00'),
            'booking_slot_end' => Setting::get('booking_slot_end', '18:00'),
            'cancellation_policy' => Setting::get('cancellation_policy', ''),
            'whatsapp_number' => Setting::get('whatsapp_number', ''),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'company_phone' => 'required|string|max:20',
            'company_email' => 'required|email|max:255',
            'company_address' => 'required|string',
            'booking_slot_start' => 'required',
            'booking_slot_end' => 'required',
            'cancellation_policy' => 'nullable|string',
            'whatsapp_number' => 'nullable|string|max:20',
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value, 'general');
        }

        return back()->with('success', 'Settings updated successfully.');
    }
}
