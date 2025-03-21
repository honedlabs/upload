<?php

declare(strict_types=1);

use Honed\Upload\Contracts\AnonymizesName;
use Honed\Upload\Upload;
use Honed\Upload\UploadData;
use Honed\Upload\UploadRule;
use Illuminate\Http\JsonResponse;

beforeEach(function () {
    $this->upload = Upload::make();
});

it('has into shorthand', function () {
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

    $upload = new class extends Upload implements AnonymizesName {
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

it('has policy options with mime types', function () {
    $key = 'test.png';

    expect(Upload::make()->types('image/*'))
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

    test('closure', function () {
        expect($this->upload->name(fn ($name, $meta) => $name . '-' . $meta['publisher']))
            ->createKey($this->data)->toBe('test-10.png');
    });

    test('path', function () {
        expect($this->upload->path('test'))
            ->createKey($this->data)
            ->toBe('test/test.png');
    });

    test('path closure', function () {
        expect($this->upload->path(fn ($meta) => '/images/' . $meta['publisher'] . '/'))
            ->createKey($this->data)
            ->toBe('images/10/test.png');
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
    expect($this->upload->toArray())
        ->toBeArray()
        ->toBeEmpty();
});
