<?php

namespace Conquest\Upload\Actions;

class Presign
{
    public function __construct() {}
    
    public static function post(UploadData $uploadData): static
    {
        return resolve(static::class, [
            'location' => $uploadData->location,
            'name' => $uploadData->name,
            'key' => $uploadData->key,
            'type' => $uploadData->type,
            'extension' => $uploadData->extension,
            'url' => $uploadData->url,
            'bytes' => $uploadData->bytes,
        ]);
    }
}