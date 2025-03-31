<?php

declare(strict_types=1);

namespace Honed\Upload\Concerns;

use Honed\Upload\Events\PresignCreated;
use Honed\Upload\Events\PresignFailed;

trait DispatchesPresignEvents
{
    /**
     * Dispatch the presign created event.
     *
     * @param  \Honed\Upload\UploadData  $data
     * @param  string  $disk
     * @return void
     */
    public static function createdPresign($data, $disk)
    {
        PresignCreated::dispatch(static::class, $data, $disk);
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public static function failedPresign($request)
    {
        PresignFailed::dispatch(static::class, $request);
    }
}
