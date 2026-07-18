<?php

declare(strict_types=1);

namespace Honed\Upload\Pipes;

use Honed\Core\Pipe;
use Honed\Upload\Events\PresignFailed;
use Honed\Upload\Upload;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * @template T of \Honed\Upload\Upload
 *
 * @extends \Honed\Core\Pipe<T>
 */
class Validate extends Pipe
{
    /**
     * Run the pipe logic.
     *
     * @throws ValidationException
     */
    public function run(Upload $instance): void
    {
        $request = $instance->getRequest();

        try {
            $rule = $instance->getRule();

            if ($rule === null && filled($instance->getRules())) {
                throw ValidationException::withMessages([
                    'type' => [__('upload::messages.type')],
                ]);
            }

            $rules = $rule?->createRules() ?? $instance->createRules();

            /** @var array{name:string,extension:string,type:string,size:int,meta:mixed} $validated */
            $validated = Validator::make(
                $request->all(),
                $rules,
                [],
                $instance->getAttributes(),
            )->validate();

            $instance->setValidated($validated);
        } catch (ValidationException $e) {
            PresignFailed::dispatch($instance::class, $request);

            throw $e;
        }
    }
}
