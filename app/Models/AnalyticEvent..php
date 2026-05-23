<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class AnalyticEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_type',
        'session_id',
        'url',
        'metadata',
        'occurred_at',
    ];

    protected $casts = [
        'metadata'    => 'array',
        'occurred_at' => 'datetime',
    ];

    public function scopeInPeriod(Builder $query, string $period): Builder
    {
        $since = match ($period) {
            '1h'  => now()->subHour(),
            '24h' => now()->subDay(),
            '7d'  => now()->subWeek(),
            default => now()->subDay(),
        };

        return $query->where('occurred_at', '>=', $since);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('event_type', $type);
    }

    public function scopeForSession(Builder $query, string $sessionId): Builder
    {
        return $query->where('session_id', $sessionId);
    }

    public static function countByType(string $period): \Illuminate\Support\Collection
    {
        return static::inPeriod($period)
            ->selectRaw('event_type, count(*) as total')
            ->groupBy('event_type')
            ->orderByDesc('total')
            ->pluck('total', 'event_type');
    }

    public static function uniqueSessionCount(string $period): int
    {
        return static::inPeriod($period)
            ->distinct('session_id')
            ->count('session_id');
    }

    public static function topUrls(string $period, int $limit = 10): \Illuminate\Support\Collection
    {
        return static::inPeriod($period)
            ->whereNotNull('url')
            ->selectRaw('url, count(*) as hits')
            ->groupBy('url')
            ->orderByDesc('hits')
            ->limit($limit)
            ->pluck('hits', 'url');
    }
}
