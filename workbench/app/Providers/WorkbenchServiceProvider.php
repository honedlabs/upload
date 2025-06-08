<?php

declare(strict_types=1);

namespace Workbench\App\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

use function Orchestra\Testbench\workbench_path;

class WorkbenchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        Config::set('filesystems', require workbench_path('config/filesystems.php'));
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
