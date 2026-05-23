<?php

namespace App\Services;

use App\Models\AnalyticEvent;
use Illuminate\Support\Facades\Cache;

class AnalyticsService
{
    public function getSummary(string $period = '24h'): array
    {
        $cacheKey = "analytics:summary:{$period}";

        return Cache::tags(['analytics'])->remember($cacheKey, now()->addMinutes(5), function () use ($period) {
            return [
                'total_events'    => AnalyticEvent::inPeriod($period)->count(),
                'unique_sessions' => AnalyticEvent::uniqueSessionCount($period),
                'by_type'         => AnalyticEvent::countByType($period),
                'period'          => $period,
                'cached_at'       => now()->toISOString(),
            ];
        });
    }
}
