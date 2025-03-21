<?php

declare(strict_types=1);

namespace Honed\Upload;

use Illuminate\Support\Arr;

class UploadData
{
    /**
     * Create a new upload data instance.
     */
    public function __construct(
        public string $name,
        public string $extension,
        public string $type,
        public int $size,
        public mixed $meta,
    ) {}

    /**
     * Create a new upload data instance from the validated data.
     *
     * @param  array<string,mixed>  $data
     * @return self
     */
    public static function from($data)
    {
        return new self(
            type($data['name'])->asString(),
            type($data['extension'])->asString(),
            type($data['type'])->asString(),
            type($data['size'])->asInt(),
            Arr::get($data, 'meta', null),
        );
    }
}
