<?php

declare(strict_types=1);

use Honed\Upload\Exceptions\PresignNotGeneratedException;

it('constructs', function () {
    $exception = new PresignNotGeneratedException();

    expect($exception)
        ->getMessage()->toBeString();
});

it('throws', function () {
    PresignNotGeneratedException::throw();
})->throws(PresignNotGeneratedException::class);
