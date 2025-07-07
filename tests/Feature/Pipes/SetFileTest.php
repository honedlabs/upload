<?php

declare(strict_types=1);

use Honed\Upload\File;
use Honed\Upload\Pipes\SetFile;
use Honed\Upload\Upload;

beforeEach(function () {
    $this->pipe = new SetFile();

    $this->upload = Upload::make();

    $this->upload->setValidated([
        'name' => 'test',
        'extension' => 'png',
        'size' => 1024,
        'type' => 'image/png',
        'meta' => [],
    ]);
});

it('sets file', function () {
    $this->pipe->through($this->upload);

    expect($this->upload->getFile())
        ->toBeInstanceOf(File::class)
        ->getName()->toBe('test')
        ->getExtension()->toBe('png')
        ->getSize()->toBe(1024)
        ->getMimeType()->toBe('image/png')
        ->getMeta()->toBe([]);

});
