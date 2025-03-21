<?php

declare(strict_types=1);

use Honed\Upload\Upload;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->upload = Upload::make()
        ->types('image/', '.png')
        ->min(1024) // 1 KB
        ->max(1024 * 2) // 2KB
        ->anonymize();
});

it('invalidates type', function () {
    $request = presignRequest('test.png', 'audio/mp3', 1024);

    $this->upload->create($request);
})->throws(ValidationException::class);

it('invalidates extension', function () {
    $request = presignRequest('test.mp3', 'image/png', 1024);

    $this->upload->create($request);
})->throws(ValidationException::class);

it('invalidates min size', function () {
    $request = presignRequest('test.png', 'image/png', 1024 - 1);

    $this->upload->create($request);
})->throws(ValidationException::class);

it('invalidates max size', function () {
    $request = presignRequest('test.png', 'image/png', 1024 * 2 + 1);

    $this->upload->create($request);
})->throws(ValidationException::class);
