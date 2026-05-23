<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function __construct(private AnalyticsService $service) {}

    public function summary(Request $request): JsonResponse
    {
        $period = $request->query('period', '24h');
        return response()->json($this->service->getSummary($period));
    }
}
