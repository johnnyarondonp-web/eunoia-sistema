<?php

namespace App\Services;

use App\Models\ExchangeRate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DolarService
{
    public const DEFAULT_RATE = 500.00;

    public function getRate(): float
    {
        // La API es siempre la fuente de verdad. La tasa manual en DB
        // es el respaldo si la API no responde, no al revés.
        $bcv = $this->getBcvRate();
        if ($bcv !== null) {
            return $bcv;
        }

        $dbRate = ExchangeRate::where('source', 'manual')->latest()->value('rate');
        if ($dbRate !== null) {
            return (float) $dbRate;
        }

        return self::DEFAULT_RATE;
    }

    public function setManualRate(float $rate): void
    {
        // Solo persiste el valor en DB como respaldo si la API falla.
        // No cachear — el caché de 7 días impedía que la API recuperara
        // su prioridad después de un ajuste manual.
        ExchangeRate::updateOrCreate(
            ['source' => 'manual'],
            ['rate'   => $rate]
        );
    }

    public function getBcvRate(): ?float
    {
        $cached = Cache::get('bcv_rate_official');
        if ($cached !== null) return (float) $cached;

        try {
            $http = Http::timeout(6);
            if (app()->environment('local')) {
                $http = $http->withoutVerifying();
            }
            $response = $http->get('https://ve.dolarapi.com/v1/dolares/oficial');

            if (!$response->successful()) {
                Log::warning('API BCV falló: ' . $response->status());
                return null;
            }

            $data = $response->json();
            $rate = $data['promedio'] ?? $data['venta'] ?? null;

            if ($rate) {
                Cache::put('bcv_rate_official', $rate, now()->addMinutes(30));
                return (float) $rate;
            }

            return null;

        } catch (\Exception $e) {
            Log::warning('Excepción al consultar API BCV: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Retorna info de diagnóstico. Usar solo desde Tinker/comandos, no exponer en UI.
     *
     * @return array
     */
    public function getDebugInfo(): array
    {
        return [
            'has_manual_cache' => Cache::has('manual_bcv_rate'),
            'manual_cache_val' => Cache::get('manual_bcv_rate'),
            'has_official_cache' => Cache::has('bcv_rate_official'),
            'official_cache_val' => Cache::get('bcv_rate_official'),
            'db_manual_latest' => ExchangeRate::where('source', 'manual')->latest()->value('rate'),
        ];
    }
}