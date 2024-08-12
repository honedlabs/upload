<?php

namespace Conquest\Upload\Http\DTOs;

use Illuminate\Http\Request;

class Presigned
{
    public function __construct(
        protected readonly string $location,
        protected readonly string $name,
        protected readonly string $key,
        protected readonly string $type,
        protected readonly string $extension,
        protected readonly string $url,
        protected readonly int $bytes,
    ) {}

    public function make(Request $request): static
    {
        return resolve(static::class, [
            'location' => $request->location,
            'name' => $request->name,
            'key' => $request->key,
            'type' => $request->type,
            'extension' => $request->extension,
            'url' => $request->url,
            'bytes' => $request->bytes,
        ]);
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getExtension(): string
    {
        return $this->extension;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getBytes(): int
    {
        return $this->bytes;
    }
}
