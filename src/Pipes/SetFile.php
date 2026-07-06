<?php

declare(strict_types=1);

namespace Honed\Upload\Pipes;

use Honed\Core\Pipe;
use Honed\Upload\File;
use Honed\Upload\Upload;

/**
 * @template T of \Honed\Upload\Upload
 *
 * @extends \Honed\Core\Pipe<T>
 */
class SetFile extends Pipe
{
    /**
     * Run the pipe logic.
     */
    public function run(Upload $instance): void
    {
        $instance->setFile(File::from($instance->getValidated()));
    }
}
