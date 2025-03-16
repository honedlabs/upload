<?php

declare(strict_types=1);

namespace Honed\Upload\Concerns;

trait HasExpires
{
    /**
     * The expiry duration of the request in seconds.
     * 
     * @var int|null
     */
    protected $expires;

    /**
     * Set the expiry duration of the request in seconds.
     * 
     * @param int $seconds
     * @return $this
     */
    public function expires($seconds)
    {
        $this->expires = $seconds;

        return $this;
    }

    /**
     * Get the expiry duration of the request in seconds.
     * 
     * @return int|null
     */
    public function getExpires()
    {
        return $this->expires;
    }
}
