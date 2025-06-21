<?php

declare(strict_types=1);

namespace Honed\Upload\Pipes;

use Honed\Core\Pipe;
use Honed\Upload\Events\PresignFailed;
use Honed\Upload\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * @extends \Honed\Core\Pipe<\Honed\Upload\Upload>
 */
class Validate extends Pipe
{
    /**
     * Run the pipe logic.
     *
     * @throws ValidationException
     */
    public function run($instance)
    {
        $request = $instance->getRequest();

        try {
            $rules = $instance->getRule()?->createRules()
                ?? $instance->createRules();

            /** @var array{name:string,extension:string,type:string,size:int,meta:mixed} $validated */
            $validated = Validator::make(
                $request->all(),
                $rules,
                [],
                $instance->getAttributes(),
            )->validate();

            $instance->setFile(File::from($validated));

        } catch (ValidationException $e) {
            PresignFailed::dispatch($instance::class, $request);

            throw $e;
        }
    }
}
