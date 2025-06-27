<?php

declare(strict_types=1);

use Honed\Upload\Upload;

beforeEach(function () {
    $this->upload = Upload::make();
});

it('can return data', function () {
    expect($this->upload)
        ->getReturn()->toBeNull()
        ->returning('test')->toBe($this->upload)
        ->getReturn()->toBe('test')
        ->returning(fn ($disk) => $disk)->toBe($this->upload)
        ->getReturn()->toBe('s3');
});
