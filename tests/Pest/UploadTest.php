<?php

declare(strict_types=1);

use Honed\Upload\Concerns\HasFilePath;
use Honed\Upload\Contracts\ShouldAnonymize;
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

it('has disk', function () {
    expect($this->upload)
        ->getDisk()->toBe(config('upload.disk'))
        ->disk('r2')->toBeInstanceOf(Upload::class)
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

it('has path', function () {
    expect(Upload::make())
        ->getPath()->toBeNull()
        ->path('test')->toBeInstanceOf(Upload::class)
        ->getPath()->toBe('test');
});

it('has name', function () {
    expect(Upload::make())
        ->getName()->toBeNull()
        ->name('test')->toBeInstanceOf(Upload::class)
        ->getName()->toBe('test');
});

it('anonymizes', function () {
    expect($this->upload)
        ->isAnonymized()->toBeFalse()
        ->anonymize()->toBe($this->upload)
        ->isAnonymized()->toBeTrue()
        ->isAnonymizedByDefault()->toBeFalse();

    $upload = new class extends Upload implements ShouldAnonymize {
        public function __construct() {}
    };

    expect($upload)
        ->isAnonymized()->toBeTrue();
});

it('has access control list', function () {
    expect(Upload::make())
        ->getACL()->toBe(config('upload.acl'))
        ->acl('private-read')->toBeInstanceOf(Upload::class)
        ->getACL()->toBe('private-read');
});

it('has returns', function () {
    expect(Upload::make())
        ->getReturns()->toBeNull()
        ->shouldReturn('test')->toBeInstanceOf(Upload::class)
        ->getReturns()->toBe('test');
});

it('has multiple', function () {
    expect(Upload::make())
        ->isMultiple()->toBeFalse()
        ->multiple()->toBeInstanceOf(Upload::class)
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

it('has form inputs', function () {
    $key = 'test';

    expect(Upload::make()->getFormInputs($key))->toEqual([
        'acl' => config('upload.acl'),
        'key' => $key,
    ]);
});

it('has policy options', function () {
    $key = 'test.png';

    expect(Upload::make())
        ->getOptions($key)->toBeArray()
        ->toHaveCount(4);
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

    test('path', function () {
        expect($this->upload->path('test'))
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