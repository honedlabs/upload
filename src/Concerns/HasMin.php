<?php

declare(strict_types=1);

namespace Honed\Upload\Concerns;

trait HasMin
{
    /**
     * The minimum file size in bytes.
     * 
     * @var int|null
     */
    protected $min;

    /**
     * Set the minimum file size in bytes.
     * 
     * @param int $size
     * @return $this
     */
    public function min($size)
    {
        $this->min = $size;

        return $this;
    }

    /**
     * Get the minimum file size in bytes.
     * 
     * @return int|null
     */
    public function getMin()
    {
        return $this->min;
    }
}
