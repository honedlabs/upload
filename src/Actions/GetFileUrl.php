<?php

namespace Conquest\Upload\Actions;

use Conquest\Upload\Http\DTOs\Presigned;

class GetFileUrl
{
    public function __invoke(string $path, string $disk, bool $temporary): string
    {
        return '';
    }
}