<?php

declare(strict_types=1);

arch()->preset()->php();

arch()->preset()->security();

arch('it will not use debugging functions')
    ->expect(['dd', 'dump', 'ray'])
    ->each->not->toBeUsed();

arch('strict types')
    ->expect('Honed\Upload')
    ->toUseStrictTypes();

arch('attributes')
    ->expect('Honed\Upload\Attributes')
    ->toBeClasses();

arch('concerns')
    ->expect('Honed\Upload\Concerns')
    ->toBeTraits();

arch('contracts')
    ->expect('Honed\Upload\Contracts')
    ->toBeInterfaces();
