<?php

declare(strict_types=1);

namespace Conquest\Upload\Concerns;


trait HasExpires
{
    protected int|null $expires = null;

    public function expires(int $expires): static
    {
        $this->setExpires($expires);

        return $this;
    }

    public function setExpires(int|null $expires): void
    {
        if (is_null($expires)) {
            return;
        }
        $this->expires = $expires;
    }

    public function hasExpires(): bool
    {
        return ! $this->lacksExpires();
    }

    public function lacksExpires(): bool
    {
        return is_null($this->expires);
    }

    public function getExpires(): int
    {
        return $this->expires ?? config('conquest-upload.expires');
    }
}
