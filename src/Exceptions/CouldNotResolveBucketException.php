<?php

declare(strict_types=1);

namespace Honed\Upload\Exceptions;

class CouldNotResolveBucketException extends \RuntimeException
{
    /**
     * Create a new could not resolve bucket exception.
     */
    public function __construct()
    {
        parent::__construct(
            'No bucket could be resolved for this upload, please check your configuration or explicitly set a bucket.'
        );
    }

    /**
     * Throw a new could not resolve bucket exception.
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
