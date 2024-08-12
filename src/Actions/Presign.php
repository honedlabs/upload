<?php

namespace Conquest\Upload\Actions;

use Conquest\Upload\Concerns\HasDisk;
use Conquest\Upload\Concerns\HasAccepts;
use Conquest\Upload\Concerns\HasExpires;
use Conquest\Upload\Concerns\HasMaxSize;
use Conquest\Upload\Concerns\HasMinSize;
use Conquest\Upload\Http\DTOs\Presigned;
use Conquest\Upload\Http\DTOs\UploadData;
use Conquest\Upload\Concerns\HasDirectory;
use Conquest\Upload\Concerns\HasModelProperty;

class Presign
{
    use HasDisk;
    use HasExpires;
    // use HasMultiple;
    use HasMaxSize;
    use HasMinSize;
    use HasAccepts;
    use HasDirectory;
    use HasModelProperty;
    // use GeneratesFileName;

    public function __construct() {}

    public static function make(): static
    {
        return resolve(static::class);
    }
    
    /**
     * @return
     */
    public static function handle(UploadData $uploadData)
    {
        // return Presigned::make(request());
        
    }
}