<?php

namespace App\Services;

use App\Models\ExchangeRate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class DolarService
{
    public const DEFAULT_RATE = 48.00;

    public function getRate(): float
    {
        // Prioridad: caché rápida → DB (tasa manual persistida) → API BCV → hardcode
        if (Cache::has('manual_bcv_rate')) {
            return (float) Cache::get('manual_bcv_rate');
        }

        $dbRate = ExchangeRate::where('source', 'manual')->latest()->value('rate');
        if ($dbRate !== null) {
            // Reconstruir caché para las próximas requests del mismo deploy
            Cache::put('manual_bcv_rate', $dbRate, now()->addDays(7));
            return (float) $dbRate;
        }

        $bcv = $this->getBcvRate();
        if ($bcv !== null) {
            return $bcv;
        }

        return self::DEFAULT_RATE;
    }

    public function setManualRate(float $rate): void
    {
        // Persiste en DB (sobrevive reinicios) y en caché (velocidad)
        ExchangeRate::create(['rate' => $rate, 'source' => 'manual']);
        Cache::put('manual_bcv_rate', $rate, now()->addDays(7));
    }

    public function getBcvRate(): ?float
    {
        $cached = Cache::get('bcv_rate_official');
        if ($cached !== null) return (float) $cached;

        try {
            $http = Http::timeout(10);
            if (app()->environment('local')) {
                $http = $http->withoutVerifying();
            }
            $response = $http->get('https://ve.dolarapi.com/v1/dolares/oficial');

            if (!$response->successful()) return null;

            $data = $response->json();
            $rate = $data['promedio'] ?? $data['venta'] ?? null;

            if ($rate) {
                Cache::put('bcv_rate_official', $rate, now()->addMinutes(30));
                return (float) $rate;
            }

            return null;

        } catch (\Exception $e) {
            return null;
        }
    }
}