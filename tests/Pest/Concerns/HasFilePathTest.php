<?php

declare(strict_types=1);

use Honed\Upload\Concerns\HasFilePath;

beforeEach(function () {
    $this->test = new class {
        use HasFilePath;
    };
});

it('gets folder', function (string $path, ?string $expected) {
    expect($this->test)
        ->getFolder($path)->toBe($expected);
})->with([
    ['test.txt', null],
    ['parent/test.txt', 'parent'],
    ['root/grandparent/parent/test.txt', 'parent'],
]);
