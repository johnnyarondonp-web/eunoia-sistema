<?php

namespace Tests\Unit;

use App\Models\ExchangeRate;
use App\Services\DolarService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery;

class DolarServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_falls_back_to_manual_rate_in_db()
    {
        // Limpiar cache para asegurar que va a la DB
        Cache::forget('manual_bcv_rate');
        Cache::forget('bcv_rate_official');

        ExchangeRate::create([
            'source' => 'manual',
            'rate' => 350.00
        ]);

        // Mockear DolarService para que getBcvRate retorne null
        $service = Mockery::mock(DolarService::class)->makePartial();
        $service->shouldReceive('getBcvRate')->andReturn(null);

        $this->assertEquals(350.00, $service->getRate());
    }

    public function test_it_falls_back_to_default_rate_constant()
    {
        Cache::forget('manual_bcv_rate');
        Cache::forget('bcv_rate_official');
        
        // Sin registros en DB
        $this->assertEquals(0, ExchangeRate::count());

        $service = Mockery::mock(DolarService::class)->makePartial();
        $service->shouldReceive('getBcvRate')->andReturn(null);

        // El valor actualizado en el paso anterior es 500.00
        $this->assertEquals(500.00, $service->getRate());
    }

    public function test_api_rate_has_priority_over_db_and_constant()
    {
        Cache::forget('manual_bcv_rate');
        Cache::forget('bcv_rate_official');

        ExchangeRate::create([
            'source' => 'manual',
            'rate' => 350.00
        ]);

        $service = Mockery::mock(DolarService::class)->makePartial();
        $service->shouldReceive('getBcvRate')->andReturn(475.50);

        $this->assertEquals(475.50, $service->getRate());
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
