<?php

declare(strict_types=1);

namespace Honed\Upload\Concerns;

use Illuminate\Support\Arr;

trait HasTypes
{
    /**
     * List of the file mime types and extensions.
     * 
     * @var array<int, string>
     */
    protected $types = [];

    /**
     * Set the file mime types and extensions.
     * 
     * @param string $types
     * @return $this
     */
    public function types(...$types)
    {
        $this->types = Arr::flatten($types);

        return $this;
    }

    /**
     * Get the accepted file mime types and extensions.
     * 
     * @return array<int, string>
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * Get the file mime types.
     * 
     * @return array<int, string>
     */
    public function getMimeTypes()
    {
        return \array_values(
            \array_filter(
                $this->getTypes(),
                static fn (string $type) => ! \str_starts_with($type, '.')
            )
        );
    }

    /**
     * Get the file extensions.
     * 
     * @return array<int, string>
     */
    public function getFileExtensions()
    {
        return \array_values(
            \array_filter(
                $this->getTypes(),
                static fn (string $type) => \str_starts_with($type, '.')
            )
        );
    }
}
