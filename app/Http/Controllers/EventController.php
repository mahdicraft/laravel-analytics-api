<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Jobs\ProcessAnalyticEvent;

class EventController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_type' => 'required|string|max:50',
            'session_id' => 'required|string|max:64',
            'url'        => 'nullable|url',
            'metadata'   => 'nullable|array',
        ]);

        // پردازش async — درخواست سریع برمی‌گرده
        ProcessAnalyticEvent::dispatch($validated);

        return response()->json(['status' => 'queued'], 202);
    }
}
