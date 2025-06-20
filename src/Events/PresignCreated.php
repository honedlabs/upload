<?php

declare(strict_types=1);

namespace Honed\Upload\Events;

use Illuminate\Foundation\Events\Dispatchable;

class PresignCreated
{
    use Dispatchable;

    /**
     * Create a new event instance.
     *
     * @param  class-string<\Honed\Upload\Upload>  $upload
     * @param  \Honed\Upload\File  $file
     * @param  string  $disk
     * @return void
     */
    public function __construct(
        public $upload,
        public $file,
        public $disk,
    ) { }
}
