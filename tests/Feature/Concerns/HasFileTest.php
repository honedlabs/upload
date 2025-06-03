<?php

declare(strict_types=1);

use Honed\Core\Concerns\Evaluable;
use Honed\Upload\Concerns\HasFile;
use Honed\Upload\Contracts\ShouldAnonymize;
use Workbench\App\Uploads\AvatarUpload;

beforeEach(function () {
    $this->test = new class()
    {
        use Evaluable;
        use HasFile;
    };

    $this->upload = AvatarUpload::make();
});

it('has disk', function () {
    expect($this->test)
        ->getDisk()->toBe(config('upload.disk'))
        ->disk('r2')->toBe($this->test)
        ->getDisk()->toBe('r2');
});

it('has location', function () {
    expect($this->test)
        ->getLocation()->toBeNull()
        ->location('test')->toBe($this->test)
        ->getLocation()->toBe('test');

    expect($this->upload)
        ->getLocation()->toBe('avatars');
});

it('has name', function () {
    expect($this->test)
        ->getName()->toBeNull()
        ->name('test')->toBe($this->test)
        ->getName()->toBe('test');
});

it('anonymizes', function () {
    expect($this->test)
        ->isAnonymized()->toBeFalse()
        ->anonymize()->toBe($this->test)
        ->isAnonymized()->toBeTrue()
        ->isAnonymizedByDefault()->toBeFalse();

    $test = new class() implements ShouldAnonymize
    {
        use HasFile;

        public function __construct() {}
    };

    expect($test)
        ->isAnonymized()->toBeTrue();
});

it('gets folder', function (string $location, ?string $expected) {
    expect($this->test)
        ->getFolder($location)->toBe($expected);
})->with([
    ['test.txt', null],
    ['parent/test.txt', 'parent'],
    ['root/grandparent/parent/test.txt', 'parent'],
]);
