<?php

declare(strict_types=1);

namespace Honed\Upload;

use Honed\Upload\Concerns\ValidatesUpload;

class UploadRule
{
    use ValidatesUpload;

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
        $mime = \is_string($mime) ? \mb_strtolower(\trim($mime)) : $mime;
        $extension = \is_string($extension) ? \mb_strtolower(\trim($extension)) : $extension;

        if (\in_array($extension, $this->getExtensions())) {
            return true;
        }

        foreach ($this->getMimeTypes() as $type) {
            if (\is_string($mime) && \str_starts_with($mime, $type)) {
                return true;
            }
        }

        return false;
    }
}
