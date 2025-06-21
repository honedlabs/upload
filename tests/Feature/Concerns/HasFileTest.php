<?php

declare(strict_types=1);

use Honed\Upload\Concerns\HasFile;
use Honed\Upload\Contracts\ShouldBeUuid;
use Honed\Upload\Exceptions\FileNotSetException;
use Honed\Upload\File;
use Honed\Upload\Upload;

beforeEach(function () {
    $this->upload = Upload::make();
});

it('has name', function () {
    expect($this->upload)
        ->getName()->toBeNull()
        ->name('test')->toBe($this->upload)
        ->getName()->toBe('test')
        ->name(fn ($name) => $name)->toBe($this->upload)
        ->getName()->toBeInstanceOf(Closure::class);
});

it('can be uuid', function () {
    expect($this->upload)
        ->isUuid()->toBeFalse()
        ->uuid()->toBe($this->upload)
        ->isUuid()->toBeTrue();
});

it('can be uuid via contract', function () {
    expect(new class() implements ShouldBeUuid
    {
        use HasFile;
    })->isUuid()->toBeTrue();
});

it('can have path', function () {
    expect($this->upload)
        ->getPathCallback()->toBeNull()
        ->path(fn ($path) => $path)->toBe($this->upload)
        ->getPathCallback()->toBeInstanceOf(Closure::class);
});

describe('file', function () {
    beforeEach(function () {
        $src = [
            'name' => 'test',
            'extension' => 'test',
            'type' => 'test',
            'size' => 100,
            'meta' => [],
        ];

        $this->file = File::from($src);
    });

    it('sets file', function () {
        $this->upload->setFile($this->file);

        expect($this->upload)
            ->getFile()->toBeInstanceOf(File::class);
    });

    it('errors when no file is set', function () {
        $this->upload->getFile();
    })->throws(FileNotSetException::class);

    it('sets path', function () {
        $this->upload
            ->path(fn (File $file) => 'test'.$file->getFilename())
            ->setFile($this->file);

        expect($this->upload)
            ->getFile()->getPath()->toBe('test'.$this->file->getFilename());
    });
});
