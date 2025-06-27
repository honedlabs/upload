<?php

declare(strict_types=1);

namespace Honed\Upload\Pipes;

use Honed\Core\Pipe;
use Honed\Upload\UploadRule;
use Illuminate\Support\Arr;

/**
 * @template T of \Honed\Upload\Upload
 *
 * @extends \Honed\Core\Pipe<T>
 */
class CreateRules extends Pipe
{
    /**
     * Run the pipe logic.
     */
    public function run(): void
    {
        $request = $this->instance->getRequest();

        [$name, $ext] = $this->separate($request->input('name'));

        $request->merge(['name' => $name, 'extension' => $ext]);

        $type = $request->input('type');

        $this->instance->setRule(
            Arr::first(
                $this->instance->getRules(),
                static fn (UploadRule $rule) => $rule->isMatching($type, $ext),
            )
        );
    }

    /**
     * Split the filename into its components.
     *
     * @param  mixed  $name
     * @return array{string|null, string|null}
     */
    public function separate($name)
    {
        if (! is_string($name)) {
            return [null, null];
        }

        return [
            pathinfo($name, PATHINFO_FILENAME),
            mb_strtolower(pathinfo($name, PATHINFO_EXTENSION)),
        ];
    }
}
