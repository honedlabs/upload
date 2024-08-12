<?php

declare(strict_types=1);

namespace Conquest\Upload\Concerns;

trait HasMinSize
{
    protected ?int $minSize = null;

    public function minSize(int $minSize): static
    {
        $this->setMinSize($minSize);

        return $this;
    }

    public function setMinSize(?int $minSize): void
    {
        if (is_null($minSize)) {
            return;
        }
        $this->minSize = $minSize;
    }

    public function hasMinSize(): bool
    {
        return ! $this->lacksMinSize();
    }

    public function lacksMinSize(): bool
    {
        return is_null($this->minSize);
    }

    public function getMinSize(): int
    {
        return $this->minSize ?? config('conquest-upload.min_size');
    }
}
