<?php

declare(strict_types=1);

namespace Honed\Upload\Concerns;

trait HasMinSize
{
    public const MIN_SIZE = 0;

    /**
     * The minimum file size in bytes.
     *
     * @var int
     */
    protected $minSize = self::MIN_SIZE;

    /**
     * Set the minimum file size in bytes.
     *
     * @param  int  $bytes
     * @return $this
     */
    public function minSize($bytes)
    {
        $this->minSize = $bytes;

        return $this;
    }

    /**
     * Get the minimum file size in bytes.
     *
     * @return int
     */
    public function getMinSize()
    {
        return $this->minSize;
    }
}
