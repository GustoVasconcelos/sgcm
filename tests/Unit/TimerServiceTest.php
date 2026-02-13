<?php

namespace Tests\Unit;

use App\Models\StudioTimer;
use App\Services\TimerService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimerServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TimerService();
    }

    public function test_get_timer_returns_existing_record(): void
    {
        // A migration já cria 1 registro
        $this->assertDatabaseCount('studio_timers', 1);

        $timer = $this->service->getTimer();

        $this->assertEquals(1, $timer->id); 
    }

    public function test_get_timer_creates_record_if_none_exists(): void
    {
        // Força remover para testar recriação
        StudioTimer::truncate();
        $this->assertDatabaseCount('studio_timers', 0);

        $timer = $this->service->getTimer();

        $this->assertDatabaseCount('studio_timers', 1);
        $this->assertInstanceOf(StudioTimer::class, $timer);
    }

    public function test_update_regressive_sets_target_time(): void
    {
        $this->service->updateRegressive('10:00:00', 'GRAVANDO');

        $timer = $this->service->getTimer();
        $this->assertEquals('GRAVANDO', $timer->mode_label);
        
        // Verifica se a hora foi setada corretamente para hoje
        $expected = Carbon::today()->setTime(10, 0, 0);
        
        // Se rodar o teste depois das 10h, ele joga pra amanhã (lógica de virada)
        if (Carbon::now()->format('H:i:s') > '10:00:00') {
            $expected->addDay();
        }

        $this->assertEquals($expected->format('Y-m-d H:i:s'), Carbon::parse($timer->target_time)->format('Y-m-d H:i:s'));
    }

    public function test_update_regressive_clears_target_when_null(): void
    {
        $this->service->updateRegressive(null, 'LIVRE');
        
        $timer = $this->service->getTimer();
        $this->assertNull($timer->target_time);
        $this->assertEquals('LIVRE', $timer->mode_label);
    }

    public function test_update_stopwatch_start(): void
    {
        $this->service->updateStopwatch('start');
        
        $timer = $this->service->getTimer();
        $this->assertEquals('running', $timer->stopwatch_status);
        $this->assertNotNull($timer->stopwatch_started_at);
    }

    public function test_update_stopwatch_pause(): void
    {
        // Fixa o tempo inicial
        Carbon::setTestNow(Carbon::now());
        
        // Start
        $this->service->updateStopwatch('start');
        
        // Avança exatos 5 segundos
        Carbon::setTestNow(Carbon::now()->addSeconds(5));

        // Pause
        $this->service->updateStopwatch('pause');

        $timer = $this->service->getTimer();
        $this->assertEquals('paused', $timer->stopwatch_status);
        $this->assertNull($timer->stopwatch_started_at);
        $this->assertEquals(5, $timer->stopwatch_accumulated_seconds);
    }

    public function test_update_stopwatch_reset(): void
    {
        $this->service->updateStopwatch('start');
        $this->service->updateStopwatch('reset');

        $timer = $this->service->getTimer();
        $this->assertEquals('stopped', $timer->stopwatch_status);
        $this->assertNull($timer->stopwatch_started_at);
        $this->assertEquals(0, $timer->stopwatch_accumulated_seconds);
    }
}
