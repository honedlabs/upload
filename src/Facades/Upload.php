<?php

namespace Conquest\Upload\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Conquest\Upload\Upload
 */
class Upload extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Conquest\Upload\Upload::class;
    }
}
