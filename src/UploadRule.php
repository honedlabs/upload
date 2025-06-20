<?php

declare(strict_types=1);

namespace Honed\Upload;

use Honed\Upload\Concerns\ValidatesUpload;
use Illuminate\Support\Arr;

use function in_array;
use function is_string;
use function str_starts_with;

class UploadRule
{
    use Concerns\ValidatesUpload;

    /**
     * Create a new file rule instance.
     *
     * @return static
     */
    public static function make()
    {
        return resolve(static::class);
    }

    /**
     * Determine if the given type matches this rule.
     *
     * @param  mixed  $mime
     * @param  mixed  $extension
     * @return bool
     */
    public function isMatching($mime, $extension)
    {
        if (in_array($extension, $this->getExtensions())) {
            return true;
        }

        return (bool) Arr::first(
            $this->getMimeTypes(), 
            static fn ($type) => is_string($mime) && str_starts_with($mime, $type)
        );
    }
}
