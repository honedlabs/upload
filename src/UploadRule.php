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
     * @param  string  $types
     * @return static
     */
    public static function make(...$types)
    {
        return resolve(static::class)
            ->types($types);
    }

    /**
     * Determine if the given type matches this rule.
     *
     * @param  mixed  ...$types
     * @return bool
     */
    public function isMatching(...$types)
    {
        return \count(\array_intersect($this->getTypes(), $types)) > 0;
    }
}
