<?php

declare(strict_types=1);

namespace Honed\Upload;

use Honed\Upload\Console\Commands\UploadMakeCommand;
use Illuminate\Support\ServiceProvider;

final class UploadServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/upload.php', 'upload');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/upload.php' => config_path('upload.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../stubs' => base_path('stubs'),
        ], 'stubs');

        if ($this->app->runningInConsole()) {
            $this->commands([
                UploadMakeCommand::class,
            ]);
        }
    }
}
