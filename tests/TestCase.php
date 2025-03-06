<?php

declare(strict_types=1);

namespace Honed\Upload\Tests;

use Honed\Upload\Tests\Fixtures\Controller;
use Honed\Upload\UploadServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            UploadServiceProvider::class,
        ];
    }

    protected function defineRoutes($router)
    {
        $router->post('/upload', [Controller::class, 'upload']);
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
    }
}
