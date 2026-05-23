<?php

namespace Tests\Feature;

use App\Jobs\ProcessAnalyticEvent;
use App\Models\AnalyticEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class EventTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_is_queued_successfully(): void
    {
        Queue::fake();

        $response = $this->postJson('/api/v1/events', [
            'event_type' => 'page_view',
            'session_id' => 'abc123',
            'url'        => 'https://example.com',
        ]);

        $response->assertStatus(202)
            ->assertJson(['status' => 'queued']);

        Queue::assertPushed(ProcessAnalyticEvent::class);
    }

    public function test_analytics_summary_is_cached(): void
    {
        Cache::tags(['analytics'])->flush();

        AnalyticEvent::factory()->count(10)->create(['event_type' => 'page_view']);

        $first  = $this->getJson('/api/v1/analytics/summary?period=24h');
        $second = $this->getJson('/api/v1/analytics/summary?period=24h');

        $first->assertStatus(200);
        $this->assertEquals($first['cached_at'], $second['cached_at']);
    }

    public function test_event_validation_fails_without_required_fields(): void
    {
        Queue::fake();

        $response = $this->postJson('/api/v1/events', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['event_type', 'session_id']);

        Queue::assertNothingPushed();
    }

    public function test_analytics_summary_returns_correct_period(): void
    {
        Cache::tags(['analytics'])->flush();

        AnalyticEvent::factory()->count(5)->create([
            'event_type'  => 'click',
            'occurred_at' => now()->subMinutes(30),
        ]);

        // این رویداد خارج از بازه ۱h هست — نباید شمارش بشه
        AnalyticEvent::factory()->create([
            'event_type'  => 'click',
            'occurred_at' => now()->subDays(2),
        ]);

        $response = $this->getJson('/api/v1/analytics/summary?period=1h');

        $response->assertStatus(200)
            ->assertJsonPath('total_events', 5)
            ->assertJsonPath('by_type.click', 5);
    }
}
