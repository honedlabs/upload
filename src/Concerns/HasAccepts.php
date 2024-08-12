<?php

declare(strict_types=1);

namespace Conquest\Upload\Concerns;

trait HasAccepts
{
    protected array $accepts = [];

    public function accepts(...$accepts): static
    {
        $this->setAccepts($accepts);

        return $this;
    }

    public function setAccepts(array|null $accepts): void
    {
        if (is_null($accepts)) {
            return;
        }
        $this->accepts = $accepts;
    }

    public function hasAccepts(): bool
    {
        return ! $this->lacksAccepts();
    }

    public function lacksAccepts(): bool
    {
        return empty($this->accepts);
    }

    public function getAccepts(): array
    {
        return $this->accepts ?? config('conquest-upload.accepts', []);
    }
}
