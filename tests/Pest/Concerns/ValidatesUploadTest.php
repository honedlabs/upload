<?php

declare(strict_types=1);

use Honed\Upload\Concerns\ValidatesUpload;

beforeEach(function () {
    $this->test = new class {
        use ValidatesUpload;
    };
});

it('has expiry', function () {
    expect($this->test)
        ->getExpiry()->toBe(config('upload.expires'))
        ->expiresIn(10)->toBe($this->test)
        ->getExpiry()->toBe(10)
        ->getDefaultExpiry()->toBe(config('upload.expires'));
});

it('formats expiry', function () {
    expect($this->test->formatExpiry(10))
        ->toBe('+10 minutes');
});

it('has max', function () {
    expect($this->test)
        ->getMax()->toBe(config('upload.max_size'))
        ->max(1000)->toBe($this->test)
        ->getMax()->toBe(1000)
        ->getDefaultMax()->toBe(config('upload.max_size'));
});

it('has min', function () {
    expect($this->test)
        ->getMin()->toBe(config('upload.min_size'))
        ->min(1000)->toBe($this->test)
        ->getMin()->toBe(1000)
        ->getDefaultMin()->toBe(config('upload.min_size'));
});

it('has mime types', function () {
    expect($this->test)
        ->getMimeTypes()->toBeEmpty()
        ->mimes('image/png')->toBe($this->test)
        ->getMimeTypes()->toBe(['image/png']);

    // Ensure it adds/removes the end /
});

it('has extensions', function () {

});

it('creates rules', function () {
    expect($this->test->createRules())
        ->toBeArray()
        ->toHaveKeys([
            'name',
            'extension',
            'size',
            'type',
            'meta',
        ]);
});

