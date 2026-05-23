<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\AnalyticEvent;
use Illuminate\Support\Facades\Cache;

class ProcessAnalyticEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private array $data) {}

    public function handle(): void
    {
        AnalyticEvent::create([
            $this->data,
            'occurred_at' => now(),
        ]);

        Cache::tags(['analytics'])->flush();
    }
}
