<?php

declare(strict_types=1);

namespace Conquest\Upload\Concerns;

trait HasMaxSize
{
    protected int|null $maxSize = null;

    public function maxSize(int $maxSize): static
    {
        $this->setMaxSize($maxSize);

        return $this;
    }

    public function setMaxSize(int|null $maxSize): void
    {
        if (is_null($maxSize)) {
            return;
        }
        $this->maxSize = $maxSize;
    }

    public function hasMaxSize(): bool
    {
        return ! $this->lacksMaxSize();
    }

    public function lacksMaxSize(): bool
    {
        return is_null($this->maxSize);
    }

    public function getMaxSize(): int
    {
        return $this->maxSize ?? config('conquest-upload.max_size');
    }
}
