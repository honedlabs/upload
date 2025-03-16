<?php

declare(strict_types=1);

namespace App\Upload;

use Honed\Upload\Concerns\HasMax;
use Honed\Upload\Concerns\HasMin;
use Honed\Upload\Concerns\HasExpires;
use Honed\Upload\Concerns\HasTypes;

class UploadRule
{
    use HasExpires;
    use HasMax;
    use HasMin;
    use HasTypes;

    /**
     * Create a new file rule instance.
     * 
     * @param string $types
     * @return static
     */
    public static function make(...$types)
    {
        return resolve(static::class)->types(...$types);
    }

    /**
     * Determine if the given type matches this rule.
     * 
     * @param string $types
     * @return bool
     */
    public function isMatching(...$types)
    {
        return \count(\array_intersect($this->getTypes(), $types)) > 0;
    }
}