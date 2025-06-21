<?php

declare(strict_types=1);

use Honed\Upload\Upload;

beforeEach(function () {
    $this->upload = Upload::make();
});

it('has lifetime', function () {
    expect($this->upload)
        ->getLifetime()->toBe(Upload::LIFETIME)
        ->lifetime(10)->toBe($this->upload)
        ->getLifetime()->toBe(10);
});

it('formats lifetime', function () {
    expect($this->upload->formatLifetime(10))
        ->toBe('+10 minutes');
});

it('has max size', function () {
    expect($this->upload)
        ->getMaxSize()->toBe(Upload::MAX_SIZE)
        ->maxSize(1000)->toBe($this->upload)
        ->getMaxSize()->toBe(1000);
});

it('has min size', function () {
    expect($this->upload)
        ->getMinSize()->toBe(Upload::MIN_SIZE)
        ->minSize(1000)->toBe($this->upload)
        ->getMinSize()->toBe(1000);
});

it('adds mime types', function () {
    expect($this->upload)
        ->getMimeTypes()->toBeEmpty()
        ->mimes('image/png')->toBe($this->upload)
        ->getMimeTypes()->toHaveCount(1)
        ->mimeTypes(['image/jpeg'])->toBe($this->upload)
        ->getMimeTypes()->toHaveCount(2);
});

it('adds mime type', function () {
    expect($this->upload)
        ->getMimeTypes()->toBeEmpty()
        ->mime('image/png')->toBe($this->upload)
        ->getMimeTypes()->toHaveCount(1)
        ->mimeType('image/jpeg')->toBe($this->upload)
        ->getMimeTypes()->toHaveCount(2);
});

it('adds extensions', function () {
    expect($this->upload)
        ->getExtensions()->toBeEmpty()
        ->extensions('png')->toBe($this->upload)
        ->getExtensions()->toHaveCount(1)
        ->extensions(['jpeg'])->toBe($this->upload)
        ->getExtensions()->toHaveCount(2);
});

it('adds extension', function () {
    expect($this->upload)
        ->getExtensions()->toBeEmpty()
        ->extension('png')->toBe($this->upload)
        ->getExtensions()->toHaveCount(1)
        ->extension('jpeg')->toBe($this->upload)
        ->getExtensions()->toHaveCount(2);
});

it('creates rules', function () {
    expect($this->upload->createRules())
        ->toBeArray()
        ->toHaveKeys([
            'name',
            'extension',
            'size',
            'type',
            'meta',
        ]);
});
