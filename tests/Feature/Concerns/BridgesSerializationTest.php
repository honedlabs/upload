<?php

declare(strict_types=1);

use Honed\Upload\Upload;

beforeEach(function () {
    $this->upload = Upload::make();
});

it('can be multiple', function () {
    expect($this->upload)
        ->isMultiple()->toBeFalse()
        ->multiple()->toBe($this->upload)
        ->isMultiple()->toBeTrue();
});

it('can have response', function () {
    expect($this->upload)
        ->respondWith('test')->toBe($this->upload)
        ->getResponse()->toBe('test')
        ->respondWith(fn ($disk) => $disk)->toBe($this->upload)
        ->getResponse()->toBe('s3');
});

it('creates a message for single uploads', function ($size, $extensions, $mimeTypes, $expected) {
    expect($this->upload)
        ->getMessage($size, $extensions, $mimeTypes)->toBe($expected);
})->with([
    [5, [], [], 'A single file up to 5 B'],
    [1024, ['jpg', 'png'], [], 'JPG, PNG up to 1 KB'],
    [1024, [], ['image/jpeg', 'image/png'], 'Image/jpeg, image/png up to 1 KB'],
    [1024, ['jpg', 'png'], ['image/jpeg', 'image/png'], 'JPG, PNG up to 1 KB'],
]);

it('creates a message for multiple uploads', function ($size, $extensions, $mimeTypes, $expected) {
    expect($this->upload->multiple())
        ->getMessage($size, $extensions, $mimeTypes)->toBe($expected);
})->with([
    [5, [], [], 'Files up to 5 B'],
]);
