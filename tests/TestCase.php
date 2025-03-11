<?php

declare(strict_types=1);

namespace Honed\Upload\Tests;

use Honed\Upload\Tests\Fixtures\Controller;
use Honed\Upload\UploadServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    /**
     * Get the package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int,class-string>
     */
    protected function getPackageProviders($app)
    {
        return [
            UploadServiceProvider::class,
        ];
    }

    /**
     * Define the routes setup.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    protected function defineRoutes($router)
    {
        $router->post('/upload', [Controller::class, 'upload']);
    }

    /**
     * Define the environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    public function getEnvironmentSetUp($app)
    {
        config()->set('filesystems', require __DIR__.'/Fixtures/filesystems.php');
        config()->set('upload', require __DIR__.'/../config/upload.php');
        config()->set('database.default', 'testing');
    }
}
