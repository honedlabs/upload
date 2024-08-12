<?php

declare(strict_types=1);

namespace Conquest\Upload\Concerns;

trait HasModelProperty
{
    /**
     * @var class-string|null
     */
    protected ?string $model = null;

    protected ?string $property = null;

    public function model(string $model, string $property): static
    {
        $this->setModel($model);
        $this->setProperty($property);

        return $this;
    }

    public function setModel(?string $model): void
    {
        if (is_null($model)) {
            return;
        }
        $this->model = $model;
    }

    public function setProperty(?string $property): void
    {
        if (is_null($property)) {
            return;
        }
        $this->property = $property;
    }

    public function getModel(): string
    {
        return $this->model ?? config('conquest-upload.model')[0];
    }

    public function getProperty(): string
    {
        return $this->property ?? config('conquest-upload.model')[1];
    }

    public function hasModel(): bool
    {
        return ! $this->lacksModel();
    }

    public function lacksModel(): bool
    {
        return is_null($this->model);
    }

    /**
     * @return array{string, string}
     */
    public function getModelProperty(): array
    {
        return [$this->getModel(), $this->getProperty()];
    }
}
