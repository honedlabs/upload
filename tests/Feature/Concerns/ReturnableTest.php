<?php

declare(strict_types=1);

use Honed\Upload\File;
use Honed\Upload\Upload;

beforeEach(function () {
    $this->upload = Upload::make();

    $this->upload->setFile(File::from([
        'name' => 'test',
        'extension' => 'png',
        'type' => 'image/png',
        'size' => 1024,
        'meta' => [],
    ]));
});

it('can return data', function () {
    expect($this->upload)
        ->getReturn()->toBe('test.png')
        ->returning('test')->toBe($this->upload)
        ->getReturn()->toBe('test')
        ->returning(fn ($disk) => $disk)->toBe($this->upload)
        ->getReturn()->toBe('s3');
});
