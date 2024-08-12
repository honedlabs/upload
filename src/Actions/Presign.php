<?php

declare(strict_types=1);

namespace Conquest\Upload\Actions;

use Conquest\Upload\Concerns\HasAccepts;
use Conquest\Upload\Concerns\HasDirectory;
use Conquest\Upload\Concerns\HasDisk;
use Conquest\Upload\Concerns\HasExpires;
use Conquest\Upload\Concerns\HasGenerator;
use Conquest\Upload\Concerns\HasMaxSize;
use Conquest\Upload\Concerns\HasMinSize;
use Conquest\Upload\Concerns\HasModelProperty;
use Conquest\Upload\Http\DTOs\Presigned;
use Conquest\Upload\Http\DTOs\UploadData;
use Illuminate\Support\Facades\Storage;

class Presign
{
    use HasAccepts;
    use HasDirectory;
    use HasDisk;
    use HasExpires;
    use HasGenerator;

    // use HasMultiple;
    use HasMaxSize;
    use HasMinSize;
    use HasModelProperty;

    public function __construct() {}

    public static function make(): static
    {
        return resolve(static::class);
    }

    public static function handle(UploadData $uploadData)
    {
        // return Presigned::make(request());
        // Storage::temporaryUploadUrl()

    }
}
