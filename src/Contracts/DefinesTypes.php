<?php

declare(strict_types=1);

namespace Honed\Upload\Contracts;

interface DefinesTypes
{
    /**
     * @return array<int, string>
     */
    public function defineMimeTypes();

    /**
     * @return array<int, string>
     */
    public function defineExtension();
}
