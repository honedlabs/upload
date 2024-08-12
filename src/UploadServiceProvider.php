<?php

namespace Conquest\Upload;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Conquest\Upload\Commands\UploadCommand;

class UploadServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('upload')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_upload_table')
            ->hasCommand(UploadCommand::class);
    }
}
