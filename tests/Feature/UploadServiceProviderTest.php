<?php

declare(strict_types=1);

use Honed\Upload\UploadServiceProvider;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    File::cleanDirectory(base_path('stubs'));
});

it('publishes stubs', function () {
    $this->artisan('vendor:publish', [
        '--provider' => UploadServiceProvider::class,
        '--tag' => 'upload-stubs',
    ])->assertSuccessful();

    $path = base_path('stubs/*.stub');

    expect(glob($path))
        ->toHaveCount(1);

    foreach (\glob(base_path('stubs/*.stub')) as $file) {
        $this->assertFileExists($file);
    }
});
