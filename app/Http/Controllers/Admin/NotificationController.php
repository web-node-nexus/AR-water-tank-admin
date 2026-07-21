<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\ServiceProvider;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = AdminNotification::with('sender')->latest()->paginate(15);

        return view('admin.notifications.index', compact('notifications'));
    }

    public function create()
    {
        $providers = ServiceProvider::where('is_active', true)->get();

        return view('admin.notifications.create', compact('providers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'target_type' => 'required|in:all,specific',
            'target_provider_ids' => 'required_if:target_type,specific|array',
            'target_provider_ids.*' => 'exists:service_providers,id',
        ]);

        AdminNotification::create([
            'title' => $validated['title'],
            'body' => $validated['body'],
            'target_type' => $validated['target_type'],
            'target_provider_ids' => $validated['target_provider_ids'] ?? null,
            'sent_by' => auth()->id(),
            'sent_at' => now(),
        ]);

        return redirect()->route('admin.notifications.index')
            ->with('success', 'Notification sent successfully.');
    }
}
