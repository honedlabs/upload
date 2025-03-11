<?php

declare(strict_types=1);

namespace Honed\Upload\Tests\Fixtures;

use Honed\Upload\Upload;
use Illuminate\Routing\Controller as BaseController;

final class Controller extends BaseController
{
    public function upload()
    {
        return Upload::make()
            ->acceptsImages()
            ->size(1, 2)
            ->kilobytes()
            ->name(fn ($meta, $name) => $name.'-'.$meta)
            ->create();
    }
}
