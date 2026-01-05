<?php

namespace EliteHub\Payment\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Filesystem\Filesystem;

class PaymentServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/payments.php', 'payments');

    }

    public function boot()
    {
        $this->publishConfig();
        $this->publishMigrations();
        $this->publishModels();
    }

    /**
     * Publica o arquivo de configuração automaticamente.
     */
    protected function publishConfig(): void
    {
        $target = config_path('payments.php');
        if (!file_exists($target)) {
            (new Filesystem)->copy(
                __DIR__ . '/../../config/payments.php',
                $target
            );
        }
    }

    /**
     * Copia as migrations do pacote para o app.
     */
    protected function publishMigrations(): void
    {
        $filesystem = new Filesystem;
        $targetDir = database_path('migrations');
        $sourceDir = __DIR__ . '/../../database/migrations';

        if (!$filesystem->isDirectory($sourceDir)) {
            return;
        }

        foreach ($filesystem->files($sourceDir) as $file) {
            $targetPath = $targetDir . '/' . $file->getFilename();

            if (!$filesystem->exists($targetPath)) {
                $filesystem->copy($file->getRealPath(), $targetPath);
            }
        }
    }

    /**
     * Copia models padrão do pacote para o app, se não existirem.
     */
    protected function publishModels(): void
    {
        $filesystem = new Filesystem;
        $sourceDir = __DIR__ . '/../../Models';
        $targetDir = app_path('Models');

        if (!$filesystem->isDirectory($sourceDir)) {
            return;
        }

        foreach ($filesystem->files($sourceDir) as $file) {
            $targetPath = $targetDir . '/' . $file->getFilename();

            if (!$filesystem->exists($targetPath)) {
                $filesystem->ensureDirectoryExists($targetDir);
                $filesystem->copy($file->getRealPath(), $targetPath);
            }
        }
    }
}
