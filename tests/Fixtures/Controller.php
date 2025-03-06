<?php

declare(strict_types=1);

namespace Honed\Upload\Tests\Fixtures;

use Honed\Upload\Upload;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

final class Controller extends BaseController
{
    public function upload(Request $request)
    {
        return Upload::make()
            ->create();
    }
}
