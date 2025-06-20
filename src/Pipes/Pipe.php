<?php

declare(strict_types=1);

namespace Honed\Upload\Pipes;

use Closure;

/**
 * @template TClass
 */
abstract class Pipe
{
    /**
     * Run the pipe logic.
     *
     * @param  TClass  $instance
     * @return void
     */
    abstract public function run($instance);

    /**
     * Apply the pipe.
     *
     * @param  TClass  $instance
     * @param  Closure(TClass): TClass  $next
     * @return TClass
     */
    public function handle($instance, $next)
    {
        $this->run($instance);

        return $next($instance);
    }
}
