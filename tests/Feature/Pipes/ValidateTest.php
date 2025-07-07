<?php

declare(strict_types=1);

use Honed\Upload\Events\PresignFailed;
use Honed\Upload\Pipes\Validate;
use Honed\Upload\Upload;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->pipe = new Validate();

    $this->upload = Upload::make()
        ->mimes('image/')
        ->extensions('png')
        ->minSize(1024)
        ->maxSize(1024 * 2)
        ->uuid();

    Event::fake();
});

it('invalidates type', function () {
    $request = presignRequest('test.png', 'audio/mp3', 1024);

    $this->pipe->instance($this->upload->request($request))->run();

    Event::assertDispatched(PresignFailed::class);

})->throws(ValidationException::class);

it('invalidates extension', function () {
    $request = presignRequest('test.mp3', 'image/png', 1024);

    $this->upload->request($request);

    $this->pipe->instance($this->upload)->run();

    Event::assertDispatched(PresignFailed::class);

})->throws(ValidationException::class);

it('invalidates min size', function () {
    $request = presignRequest('test.png', 'image/png', 1024 - 1);

    $this->upload->request($request);

    $this->pipe->instance($this->upload)->run();

    Event::assertDispatched(PresignFailed::class);

})->throws(ValidationException::class);

it('invalidates max size', function () {
    $request = presignRequest('test.png', 'image/png', 1024 * 2 + 1);

    $this->pipe->instance($this->upload->request($request))->run();

    Event::assertDispatched(PresignFailed::class);

})->throws(ValidationException::class);

it('validates type', function () {
    $request = presignRequest('test.png', 'image/png', 1024);

    $this->upload->request($request);

    $this->pipe->instance($this->upload)->run();

    Event::assertNotDispatched(PresignFailed::class);
});
