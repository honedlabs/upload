<?php

namespace Conquest\Upload\Actions;

use Conquest\Upload\Http\DTOs\Presigned;

class Presign
{
    // use HasDisk;
    // use HasExpires;
    // use HasMultiple;
    // use HasMaxSize;
    // use HasMinSize;
    // use HasAccepts;
    // use HasDirectory;
    // use HasModelProperty;
    // use GeneratesFileName;

    public function __construct() {}

    public static function make(): static
    {
        return resolve(static::class);
    }
    
    /**
     * @return
     */
    public static function post(UploadData $uploadData): Presigned
    {
        
    }
}