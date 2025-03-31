<?php

declare(strict_types=1);

namespace Honed\Upload\Events;

use Illuminate\Foundation\Events\Dispatchable;

class PresignFailed
{
    use Dispatchable;

    /**
     * Create a new event instance.
     *
     * @param  class-string<\Honed\Upload\Upload>  $upload
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function __construct(
        public $upload,
        public $request,
    ) {
        //
    }
}
