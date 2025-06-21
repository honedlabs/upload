<?php

declare(strict_types=1);

use Honed\Upload\Exceptions\CouldNotResolveBucketException;

it('constructs', function () {
    $exception = new CouldNotResolveBucketException();

    expect($exception)
        ->getMessage()->toBeString();
});

it('throws', function () {
    CouldNotResolveBucketException::throw();
})->throws(CouldNotResolveBucketException::class);
