<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use App\Services\DolarService;
use App\Models\ExchangeRate;

class DolarServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_retorna_tasa_desde_cache_si_existe(): void
    {
        Cache::put('bcv_rate_official', 42.50, 600);

        $service = app(DolarService::class);
        $this->assertEquals(42.50, $service->getBcvRate());
    }

    public function test_retorna_tasa_manual_de_db_si_no_hay_cache(): void
    {
        Cache::forget('manual_bcv_rate');
        ExchangeRate::create(['source' => 'manual', 'rate' => 55.00]);

        $service = app(DolarService::class);
        $this->assertEquals(55.00, $service->getRate());
    }

    public function test_retorna_fallback_si_todo_falla(): void
    {
        Cache::forget('bcv_rate_official');
        Cache::forget('manual_bcv_rate');
        Http::fake(['*' => Http::response(null, 500)]);

        $service = app(DolarService::class);
        $rate = $service->getBcvRate();

        $this->assertNull($rate); // getBcvRate retorna null si la API falla
    }

    public function test_getrate_cae_a_default_si_todo_falla(): void
    {
        Cache::forget('bcv_rate_official');
        Cache::forget('manual_bcv_rate');
        Http::fake(['*' => Http::response(null, 500)]);

        $service = app(DolarService::class);
        $this->assertEquals(DolarService::DEFAULT_RATE, $service->getRate());
    }

    public function test_setManualRate_solo_guarda_un_registro(): void
    {
        $service = app(DolarService::class);

        $service->setManualRate(50.00);
        $service->setManualRate(60.00);
        $service->setManualRate(70.00);

        // updateOrCreate garantiza exactamente un registro manual en la tabla
        $this->assertEquals(1, ExchangeRate::where('source', 'manual')->count());
        $this->assertEquals(70.00, ExchangeRate::where('source', 'manual')->value('rate'));
    }
}
