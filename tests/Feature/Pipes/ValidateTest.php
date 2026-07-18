<?php

declare(strict_types=1);

use Honed\Upload\Events\PresignFailed;
use Honed\Upload\Pipes\CreateRules;
use Honed\Upload\Pipes\Validate;
use Honed\Upload\Upload;
use Honed\Upload\UploadRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;

describe('single rule', function () {
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

        $this->pipe->run($this->upload->request($request));

        Event::assertDispatched(PresignFailed::class);

    })->throws(ValidationException::class);

    it('invalidates extension', function () {
        $request = presignRequest('test.mp3', 'image/png', 1024);

        $this->upload->request($request);

        $this->pipe->run($this->upload);

        Event::assertDispatched(PresignFailed::class);

    })->throws(ValidationException::class);

    it('invalidates both', function () {
        $request = presignRequest('test.mp3', 'audio/mp3', 1024);

        $this->upload->request($request);

        $this->pipe->run($this->upload);

        Event::assertDispatched(PresignFailed::class);

    })->throws(ValidationException::class);

    it('invalidates min size', function () {
        $request = presignRequest('test.png', 'image/png', 1024 - 1);

        $this->upload->request($request);

        $this->pipe->run($this->upload);

        Event::assertDispatched(PresignFailed::class);

    })->throws(ValidationException::class);

    it('invalidates max size', function () {
        $request = presignRequest('test.png', 'image/png', 1024 * 2 + 1);

        $this->pipe->run($this->upload->request($request));

        Event::assertDispatched(PresignFailed::class);

    })->throws(ValidationException::class);

    it('validates type', function () {
        $request = presignRequest('test.png', 'image/png', 1024);

        $this->upload->request($request);

        $this->pipe->run($this->upload);

        Event::assertNotDispatched(PresignFailed::class);
    });
});

describe('multiple rules', function () {
    beforeEach(function () {
        $this->createRules = new CreateRules();
        $this->validate = new Validate();

        $this->upload = Upload::make()
            ->rules([
                UploadRule::make()
                    ->mimes(['image/jpeg', 'image/png'])
                    ->extensions(['jpg', 'jpeg', 'png'])
                    ->minSize(1024)
                    ->maxSize(2048),

                UploadRule::make()
                    ->mimes(['application/pdf'])
                    ->extensions(['pdf'])
                    ->minSize(1024)
                    ->maxSize(4096),
            ]);

        Event::fake();
    });

    it('fails validation', function (string $file, string $type, int $size) {
        $request = presignRequest($file, $type, $size);

        $this->upload->request($request);

        $this->createRules->run($this->upload);
        $this->validate->run($this->upload);

        Event::assertDispatched(PresignFailed::class);
    })
        ->throws(ValidationException::class)
        ->with([
            ['test.doc', 'application/msword', 1680],
            ['test.jpg', 'image/jpeg', 2049],
            ['test.jpg', 'image/jpeg', 1023],
            ['test.pdf', 'application/pdf', 4097],
            ['test.pdf', 'application/pdf', 2047],
            ['test.jpg', 'audio/mp3', 1024],
            ['test.gif', 'image/jpeg', 1024],
        ]);

    it('passes validation', function (string $file, string $type, int $size) {
        $request = Request::create('/', 'GET', [
            'name' => $file,
            'type' => $type,
            'size' => $size,
        ]);

        $this->upload->request($request);

        $this->createRules->run($this->upload);
        $this->validate->run($this->upload);

        Event::assertNotDispatched(PresignFailed::class);
    })->with([
        ['test.jpg', 'image/jpeg', 1024],
        ['test.png', 'image/png', 2048],
        ['test.pdf', 'application/pdf', 2048],
        ['test.pdf', 'application/pdf', 4096],
    ]);
})->only();
