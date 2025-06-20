<?php

declare(strict_types=1);

namespace Honed\Upload\Exceptions;

use RuntimeException;

class PresignNotGeneratedException extends RuntimeException
{
    /**
     * Create a new presign not generated exception.
     */
    public function __construct()
    {
        parent::__construct(
            'The presign has not been generated.'
        );
    }

    /**
     * Throw a new presign not generated exception.
     *
     * @return never
     *
     * @throws static
     */
    public static function throw()
    {
        throw new self();
    }
}
