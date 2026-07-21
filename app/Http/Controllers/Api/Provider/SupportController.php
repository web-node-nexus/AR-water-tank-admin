<?php

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tickets = SupportTicket::where('provider_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json(['data' => $tickets]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        $ticket = SupportTicket::create([
            ...$validated,
            'provider_id' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Support ticket created. Admin will respond soon.',
            'data' => $ticket,
        ], 201);
    }
}
