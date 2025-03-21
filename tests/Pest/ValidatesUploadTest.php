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
    expect($this->test->expiresIn(10)->formatExpiry())
        ->toBe('+10 seconds');
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

it('has types', function () {
    expect($this->test)
        ->getTypes()->toBeEmpty()
        ->types('image/png', 'image/jpeg')->toBe($this->test)
        ->getTypes()->toBe(['image/png', 'image/jpeg'])
        ->types(['image/gif'])->toBe($this->test)
        ->getTypes()->toBe(['image/png', 'image/jpeg', 'image/gif']);
});

it('sets types to only images', function () {
    expect($this->test->onlyImages())
        ->getTypes()->toEqual([
            'image/',
        ]);
});

it('sets types to only videos', function () {
    expect($this->test->onlyVideos())
        ->getTypes()->toEqual([
            'video/',
        ]);
});

it('sets types to only audio', function () {
    expect($this->test->onlyAudio())
        ->getTypes()->toEqual([
            'audio/',
        ]);
});

it('sets types to only pdf', function () {
    expect($this->test->onlyPdf())
        ->getTypes()->toEqual([
            'application/pdf',
            '.pdf',
        ]);
});

it('separates mimes and extensions', function () {
    expect($this->test)
        ->types('image/png', '.jpeg')->toBe($this->test)
        ->getMimes()->toBe(['image/png'])
        ->getExtensions()->toBe(['.jpeg']); 
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

