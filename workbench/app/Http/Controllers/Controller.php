<?php

declare(strict_types=1);

namespace Workbench\App\Http\Controllers;

use Honed\Upload\Upload;
use Illuminate\Routing\Controller as BaseController;

final class Controller extends BaseController
{
    public function upload()
    {
        return Upload::make()
            ->onlyImages()
            ->name(fn ($meta, $name) => $name.'-'.$meta)
            ->create();
    }
}
