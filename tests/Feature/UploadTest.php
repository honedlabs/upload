<?php

declare(strict_types=1);

use Honed\Upload\Upload;
use Honed\Upload\UploadData;
use Honed\Upload\UploadRule;
use Illuminate\Http\JsonResponse;

beforeEach(function () {
    $this->upload = Upload::make();
});

it('uploads into', function () {
    expect(Upload::into('r2'))
        ->getDisk()->toBe('r2');
});

it('has rules', function () {
    expect($this->upload)
        ->getRules()->toBeEmpty()
        ->rules(UploadRule::make('image/png'))->toBe($this->upload)
        ->getRules()->toHaveCount(1)
        ->rules([UploadRule::make('image/jpeg')])->toBe($this->upload)
        ->getRules()->toHaveCount(2);
});

it('has access control list', function () {
    expect($this->upload)
        ->getACL()->toBe(config('upload.acl'))
        ->acl('private-read')->toBe($this->upload)
        ->getACL()->toBe('private-read');
});

it('provides', function () {
    expect($this->upload)
        ->getProvided()->toEqual([])
        ->provide('test')->toBe($this->upload)
        ->getProvided()->toBe('test');
});

it('has multiple', function () {
    expect($this->upload)
        ->isMultiple()->toBeFalse()
        ->multiple()->toBe($this->upload)
        ->isMultiple()->toBeTrue();
});

it('has message', function () {
    expect($this->upload)
        ->onlyMessage()->toBeFalse()
        ->message()->toBe($this->upload)
        ->onlyMessage()->toBeTrue()
        ->toArray()->toHaveKeys([
            'multiple',
            'message',
        ]);
});

it('gets data', function () {
    expect($this->upload)
        ->getData()->toBeNull();

    $request = presignRequest('test.png', 'image/png', 1024);

    $this->upload->create($request);

    expect($this->upload)
        ->getData()->toBeInstanceOf(UploadData::class);
});

it('has form inputs', function () {
    $key = 'test';

    expect(Upload::make()->getFormInputs($key))->toEqual([
        'acl' => config('upload.acl'),
        'key' => $key,
    ]);
});

it('has policy options', function () {
    $key = 'test.png';

    expect($this->upload)
        ->getOptions($key)->toBeArray()
        ->toHaveCount(5);
});

it('destructures filenames', function () {
    expect(Upload::destructureFilename('test.png'))
        ->toBe(['test', 'png']);

    expect(Upload::destructureFilename(null))
        ->toBe([null, null]);
});

describe('key creation', function () {
    beforeEach(function () {
        $this->data = new UploadData(
            'test',
            'png',
            'image/png',
            1024,
            ['publisher' => 10]
        );
    });

    test('basic', function () {
        expect($this->upload)
            ->createKey($this->data)->toBe('test.png');
    });

    test('anonymized', function () {
        expect($this->upload->anonymize())
            ->createKey($this->data)->not->toBe('test.png');
    });

    test('location', function () {
        expect($this->upload->location('test'))
            ->createKey($this->data)
            ->toBe('test/test.png');
    });
});

it('creates', function () {
    $request = presignRequest('test.png', 'image/png', 1024);

    expect($this->upload->create($request))
        ->toBeArray()
        ->toHaveKeys([
            'attributes',
            'inputs',
        ]);
});

it('is response', function () {
    $request = presignRequest('test.png', 'image/png', 1024);

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

it('resolves closures by name', function () {
    $request = presignRequest('test.png', 'image/png', 1024);
    $upload = Upload::make();
    $upload->create($request);

    expect($upload)
        ->evaluate(fn ($bucket) => $bucket)->toBe('test')
        ->evaluate(fn ($data) => $data)->toBeInstanceOf(UploadData::class)
        ->evaluate(fn ($extension) => $extension)->toBe('png')
        ->evaluate(fn ($type) => $type)->toBe('image/png')
        ->evaluate(fn ($size) => $size)->toBe(1024)
        ->evaluate(fn ($meta) => $meta)->toBeNull()
        ->evaluate(fn ($disk) => $disk)->toBe(config('upload.disk'))
        ->evaluate(fn ($key) => $key)->toBe('test.png')
        ->evaluate(fn ($file) => $file)->toBe('test.png')
        ->evaluate(fn ($filename) => $filename)->toBe('test')
        ->evaluate(fn ($folder) => $folder)->toBeNull()
        ->evaluate(fn ($name) => $name)->toBe('test')
        ->evaluate(fn ($extension) => $extension)->toBe('png')
        ->evaluate(fn ($type) => $type)->toBe('image/png')
        ->evaluate(fn ($size) => $size)->toBe(1024)
        ->evaluate(fn ($meta) => $meta)->toBeNull()
        ->evaluate(fn ($disk) => $disk)->toBe(config('upload.disk'));
});

it('resolves closures by type', function () {
    $request = presignRequest('test.png', 'image/png', 1024);
    $upload = Upload::make();
    $upload->create($request);

    expect($upload)
        ->evaluate(fn (UploadData $d) => $d)->toBeInstanceOf(UploadData::class);
});
