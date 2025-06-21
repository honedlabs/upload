<?php

declare(strict_types=1);

use Honed\Upload\File;

beforeEach(function () {
    $this->data = [
        'name' => 'test',
        'extension' => 'png',
        'type' => 'image/png',
        'size' => 1024,
        'meta' => null,
    ];

    $this->file = File::from($this->data);
});

it('sets name', function () {
    expect($this->file)
        ->getName()->toBe($this->data['name'])
        ->name('new')->toBe($this->file)
        ->getName()->toBe('new');
});

it('sets extension', function () {
    expect($this->file)
        ->getExtension()->toBe($this->data['extension'])
        ->extension('jpg')->toBe($this->file)
        ->getExtension()->toBe('jpg');
});

it('gets filename', function () {
    $ext = $this->data['extension'];

    expect($this->file)
        ->getFilename()->toBe($this->data['name'].'.'.$ext)
        ->name('new')->toBe($this->file)
        ->getFilename()->toBe('new.'.$ext);
});

it('sets mime type', function () {
    expect($this->file)
        ->getMimeType()->toBe($this->data['type'])
        ->mimeType('image/jpeg')->toBe($this->file)
        ->getMimeType()->toBe('image/jpeg');
});

it('sets size', function () {
    expect($this->file)
        ->getSize()->toBe($this->data['size'])
        ->size(2048)->toBe($this->file)
        ->getSize()->toBe(2048);
});

it('sets meta', function () {
    expect($this->file)
        ->getMeta()->toBeNull()
        ->meta('test')->toBe($this->file)
        ->getMeta()->toBe('test');
});

it('sets path', function () {
    expect($this->file)
        ->getPath()->toBe($this->data['name'].'.'.$this->data['extension'])
        ->path('test')->toBe($this->file)
        ->getPath()->toBe('test');
});
