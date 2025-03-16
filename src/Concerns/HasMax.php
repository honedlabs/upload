<?php

declare(strict_types=1);

namespace Honed\Upload\Concerns;

trait HasMax
{
    /**
     * The maximum file size in bytes.
     * 
     * @var int|null
     */
    protected $max;

    /**
     * Set the maximum file size in bytes.
     * 
     * @param int $size
     * @return $this
     */
    public function max($size)
    {
        $this->max = $size;

        return $this;
    }

    /**
     * Get the maximum file size in bytes.
     * 
     * @return int|null
     */
    public function getMax()
    {
        return $this->max;
    }
}
