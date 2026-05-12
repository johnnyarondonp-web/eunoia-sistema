<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class BackupDatabase extends Command
{
    protected $signature   = 'db:backup';
    protected $description = 'Exporta la base de datos a storage/app/backups/. Conserva los últimos 30 archivos.';

    public function handle(): int
    {
        $host     = env('DB_HOST', '127.0.0.1');
        $port     = env('DB_PORT', '3306');
        $database = env('DB_DATABASE');
        $user     = env('DB_USERNAME');
        $password = env('DB_PASSWORD');

        $dir = storage_path('app/backups');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filename = $dir . '/backup-' . now()->format('Y-m-d') . '.sql';

        // Construir comando sin mostrar la contraseña en logs de proceso
        $cmd = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s %s > %s 2>&1',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($user),
            escapeshellarg($password),
            escapeshellarg($database),
            escapeshellarg($filename)
        );

        exec($cmd, $output, $exitCode);

        if ($exitCode !== 0) {
            Log::error('BackupDatabase: mysqldump falló.', ['output' => implode("\n", $output)]);
            $this->error('El backup falló. Revisa storage/logs/laravel.log.');
            return self::FAILURE;
        }

        $this->info("Backup guardado en: {$filename}");

        // Mantener solo los últimos 30 backups
        $files = glob($dir . '/backup-*.sql');
        if (count($files) > 30) {
            sort($files); // los más antiguos quedan primero
            $toDelete = array_slice($files, 0, count($files) - 30);
            foreach ($toDelete as $old) {
                unlink($old);
            }
            $this->info('Backups antiguos eliminados: ' . count($toDelete));
        }

        return self::SUCCESS;
    }
}
