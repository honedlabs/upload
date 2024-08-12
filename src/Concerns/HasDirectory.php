<?php

declare(strict_types=1);

namespace Conquest\Upload\Concerns;

trait HasDirectory
{
    protected ?string $directory = null;

    public function directory(string $directory): static
    {
        $this->setDirectory($directory);

        return $this;
    }

    public function setDirectory(?string $directory): void
    {
        if (is_null($directory)) {
            return;
        }
        $this->directory = $directory;
    }

    public function hasDirectory(): bool
    {
        return ! $this->lacksDirectory();
    }

    public function lacksDirectory(): bool
    {
        return is_null($this->directory);
    }

    public function getDirectory(): string
    {
        return trim($this->directory, ' /');
    }
}
