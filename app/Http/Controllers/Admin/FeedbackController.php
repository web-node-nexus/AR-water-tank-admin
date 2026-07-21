<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function index(Request $request)
    {
        $query = Feedback::with(['booking', 'customer', 'provider']);

        if ($request->filled('flagged')) {
            $query->where('is_flagged', true);
        }

        $feedback = $query->latest()->paginate(15)->withQueryString();

        return view('admin.feedback.index', compact('feedback'));
    }

    public function respond(Request $request, Feedback $feedback)
    {
        $validated = $request->validate([
            'admin_response' => 'required|string|max:1000',
            'is_flagged' => 'boolean',
        ]);

        $feedback->update([
            'admin_response' => $validated['admin_response'],
            'is_flagged' => $request->boolean('is_flagged'),
        ]);

        return back()->with('success', 'Response saved.');
    }

    public function toggleFlag(Feedback $feedback)
    {
        $feedback->update(['is_flagged' => ! $feedback->is_flagged]);

        return back()->with('success', 'Flag status updated.');
    }
}
