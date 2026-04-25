<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DolarService
{
    public function getBcvRate()
    {
        // Bajamos el tiempo de caché a 10 minutos (600s) para pruebas, luego lo subes a 3600
        return Cache::remember('bcv_rate', 600, function () {
            try {
                // Agregamos withoutVerifying() por si tu entorno local no tiene certificados SSL actualizados
                $response = Http::timeout(10)
                    ->withoutVerifying() 
                    ->get('https://ve.dolarapi.com/v1/dolares/oficial');
                
                if ($response->successful()) {
                    $data = $response->json();
                    
                    // Verificamos que la llave 'promedio' exista antes de usarla
                    if (isset($data['promedio'])) {
                        return (float) $data['promedio'];
                    }
                    
                    Log::error("DolarApi: La respuesta no contiene la llave 'promedio'.");
                }

                Log::error("DolarApi falló con estado: " . $response->status());
                return null;

            } catch (\Exception $e) {
                // Esto nos dirá exactamente qué pasó en storage/logs/laravel.log
                Log::error("Error crítico en DolarService: " . $e->getMessage());
                return null;
            }
        });
    }
}