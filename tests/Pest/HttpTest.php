<?php

declare(strict_types=1);

use function Pest\Laravel\post;

it('requires fields', function () {
    post('/upload')
        ->assertInvalid([
            'name' => 'The file name field is required.',
            'type' => 'The file type field is required.',
            'size' => 'The file size field is required.',
        ]);
});

it('invalidates type', function () {
    post('/upload', [
        'name' => 'test.mp3',
        'type' => 'audio/mp3',
        'size' => 1024,
    ])->assertInvalid([
        'type' => 'The file type is not supported.',
    ]);
});

it('validates type', function () {
    post('/upload', [
        'name' => 'test.mp4',
        'type' => 'image/png',
        'size' => 1024,
    ])->assertValid();
});

it('invalidates max size', function () {
    post('/upload', [
        'name' => 'test.png',
        'type' => 'image/png',
        'size' => 1024 ** 3,
    ])->assertInvalid([
        'size' => 'The file size must be smaller than 2 kilobytes.',
    ]);
});

it('invalidates min size', function () {
    post('/upload', [
        'name' => 'test.png',
        'type' => 'image/png',
        'size' => 1023,
    ])->assertInvalid([
        'size' => 'The file size must be larger than 1 kilobyte.',
    ]);
});

it('validates size', function () {
    post('/upload', [
        'name' => 'test.png',
        'type' => 'image/png',
        'size' => 1024,
    ])->assertValid();
});