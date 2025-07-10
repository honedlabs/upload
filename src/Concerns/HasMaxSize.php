<?php

declare(strict_types=1);

namespace Honed\Upload\Concerns;

trait HasMaxSize
{
    public const MAX_SIZE = 2147483647;

    /**
     * The maximum file size in bytes.
     *
     * @var int
     */
    protected $maxSize = self::MAX_SIZE;

    /**
     * Set the maximum file size in bytes.
     *
     * @param  int  $bytes
     * @return $this
     */
    public function maxSize($bytes)
    {
        $this->maxSize = $bytes;

        return $this;
    }

    /**
     * Get the maximum file size in bytes.
     *
     * @return int
     */
    public function getMaxSize()
    {
        return $this->maxSize;
    }
}
