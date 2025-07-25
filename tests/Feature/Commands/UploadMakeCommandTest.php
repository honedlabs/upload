<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

beforeEach(function () {
    File::cleanDirectory(app_path('Uploads'));
});

it('makes', function () {
    $this->artisan('make:upload', [
        'name' => 'ProfileUpload',
        '--force' => true,
    ])->assertSuccessful();

    $this->assertFileExists(app_path('Uploads/ProfileUpload.php'));
});

it('prompts for a name', function () {
    $this->artisan('make:upload', [
        '--force' => true,
    ])->expectsQuestion('What should the upload be named?', 'DocumentUpload')
        ->assertSuccessful();

    $this->assertFileExists(app_path('Uploads/DocumentUpload.php'));
});
