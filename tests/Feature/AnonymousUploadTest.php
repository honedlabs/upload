<?php

declare(strict_types=1);

use Honed\Upload\File;
use Honed\Upload\Upload;
use Honed\Upload\UploadRule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

beforeEach(function () {
    $this->upload = Upload::make();
});

it('uploads into a disk', function () {
    expect(Upload::into('r2'))
        ->getDisk()->toBe('r2');
});

it('responds with json', function () {
    $request = Request::create('/', Request::METHOD_GET, [
        'name' => 'test.png',
        'type' => 'image/png',
        'size' => 1024,
    ]);

    expect($this->upload->toResponse($request))
        ->toBeInstanceOf(JsonResponse::class);
});

it('has array representation', function () {
    expect($this->upload)
        ->toArray()->toHaveKeys([
            'multiple',
            'message',
            'extensions',
            'mimes',
            'size',
        ]);
});

describe('evaluation', function () {
    beforeEach(function () {
        $this->upload->setFile(new File());
        $this->upload->setRule(new UploadRule());
    });

    it('names dependencies classes', function ($closure, $class) {
        expect($this->upload)
            ->evaluate($closure)->toBeInstanceOf($class);
    })->with([
        fn () => [fn ($file) => $file, File::class],
        fn () => [fn ($rule) => $rule, UploadRule::class],
    ]);

    it('names dependencies', function ($closure, $value) {
        expect($this->upload)
            ->evaluate($closure)->toBe($value);
    })->with([
        fn () => [fn ($disk) => $disk, 's3'],
        fn () => [fn ($bucket) => $bucket, 'test'],
    ]);

    it('typed dependencies', function ($closure, $class) {
        expect($this->upload->evaluate($closure))->toBeInstanceOf($class);
    })->with([
        fn () => [fn (File $arg) => $arg, File::class],
        fn () => [fn (UploadRule $arg) => $arg, UploadRule::class],
    ]);
});
