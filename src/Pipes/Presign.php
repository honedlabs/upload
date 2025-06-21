<?php

declare(strict_types=1);

namespace Honed\Upload\Pipes;

use Aws\S3\PostObjectV4;
use Honed\Core\Pipe;
use Honed\Upload\Events\PresignCreated;

/**
 * @extends \Honed\Core\Pipe<\Honed\Upload\Upload>
 */
class Presign extends Pipe
{
    /**
     * Run the pipe logic.
     */
    public function run($instance)
    {
        $lifetime = $instance->getRule()?->getLifetime()
            ?? $instance->getLifetime();

        $file = $instance->getFile();

        $instance->setPresign(new PostObjectV4(
            $instance->getClient(),
            $instance->getBucket(),
            $instance->getFormInputs($file->getPath()),
            $instance->getOptions(
                $file->getPath(), $file->getMimeType(), $file->getSize()
            ),
            $instance->formatLifetime($lifetime),
        ));

        PresignCreated::dispatch(
            $instance::class, $file, $instance->getDisk()
        );
    }
}
