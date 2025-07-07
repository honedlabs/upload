<?php

declare(strict_types=1);

namespace Honed\Upload\Concerns;

trait HasValidatedInput
{
    /**
     * The validated input.
     *
     * @var array{name:string,extension:string,type:string,size:int,meta:mixed}
     */
    protected $validated;

    /**
     * Set the validated input.
     *
     * @param  array{name:string,extension:string,type:string,size:int,meta:mixed}  $validated
     */
    public function setValidated(array $validated): void
    {
        $this->validated = $validated;
    }

    /**
     * Get the validated input.
     *
     * @return array{name:string,extension:string,type:string,size:int,meta:mixed}
     */
    public function getValidated(): array
    {
        return $this->validated;
    }
}
