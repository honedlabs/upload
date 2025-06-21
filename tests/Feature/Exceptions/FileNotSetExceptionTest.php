<?php

declare(strict_types=1);

use Honed\Upload\Exceptions\FileNotSetException;

it('constructs', function () {
    $exception = new FileNotSetException();

    expect($exception)
        ->getMessage()->toBeString();
});

it('throws', function () {
    FileNotSetException::throw();
})->throws(FileNotSetException::class);
