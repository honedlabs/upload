<?php

declare(strict_types=1);

namespace Honed\Upload\Exceptions;

class FileNotSetException extends \RuntimeException
{
    /**
     * Create a new file not set exception.
     */
    public function __construct()
    {
        parent::__construct(
            'You cannot access the file before it has been set.'
        );
    }

    /**
     * Throw a new file not set exception.
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
