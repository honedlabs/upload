<?php

declare(strict_types=1);

use Honed\Upload\UploadServiceProvider;

it('publishes stubs', function () {
    $this->artisan('vendor:publish', [
        '--provider' => UploadServiceProvider::class,
        '--tag' => 'stubs',
    ])->assertSuccessful();

    $path = base_path('stubs/*.stub');

    expect(glob($path))
        ->toHaveCount(1);

    foreach (\glob(base_path('stubs/*.stub')) as $file) {
        $this->assertFileExists($file);
    }
});
