<?php

declare(strict_types=1);

namespace Honed\Upload\Pipes;

use Honed\Core\Pipe;
use Honed\Upload\Events\PresignFailed;
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
    public function run(): void
    {
        $request = $this->instance->getRequest();

        try {
            $rules = $this->instance->getRule()?->createRules()
                ?? $this->instance->createRules();

            /** @var array{name:string,extension:string,type:string,size:int,meta:mixed} $validated */
            $validated = Validator::make(
                $request->all(),
                $rules,
                [],
                $this->instance->getAttributes(),
            )->validate();

            $this->instance->setValidated($validated);
        } catch (ValidationException $e) {
            PresignFailed::dispatch($this->instance::class, $request);

            throw $e;
        }
    }
}
