<?php

declare(strict_types=1);

namespace Honed\Upload;

use Honed\Upload\Commands\UploadMakeCommand;
use Illuminate\Support\ServiceProvider;

class UploadServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'upload');

        if ($this->app->runningInConsole()) {
            $this->offerPublishing();

            $this->commands([
                UploadMakeCommand::class,
            ]);
        }
    }

    /**
     * Register the publishing for the package.
     *
     * @return void
     */
    public function offerPublishing()
    {
        $this->publishes([
            __DIR__.'/../stubs' => base_path('stubs'),
        ], 'upload-stubs');

        $this->publishes([
            __DIR__.'/../lang' => lang_path('vendor/upload'),
        ], 'upload-lang');
    }
}
