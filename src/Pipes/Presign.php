<?php

declare(strict_types=1);

namespace Honed\Upload\Pipes;

use Aws\S3\PostObjectV4;
use Honed\Core\Pipe;
use Honed\Upload\Events\PresignCreated;

/**
 * @template T of \Honed\Upload\Upload
 *
 * @extends \Honed\Core\Pipe<T>
 */
class Presign extends Pipe
{
    /**
     * Run the pipe logic.
     */
    public function run(): void
    {
        $lifetime = $this->instance->getRule()?->getLifetime()
            ?? $this->instance->getLifetime();

        $file = $this->instance->getFile();

        $this->instance->setPresign(new PostObjectV4(
            $this->instance->getClient(),
            $this->instance->getBucket(),
            $this->instance->getFormInputs($file->getPath()),
            $this->instance->getOptions(
                $file->getPath(), $file->getMimeType(), $file->getSize()
            ),
            $this->instance->formatLifetime($lifetime),
        ));

        PresignCreated::dispatch(
            $this->instance::class, $file, $this->instance->getDisk()
        );
    }
}
