<?php

declare(strict_types=1);

use Honed\Upload\Upload;
use Illuminate\Support\Str;

it('has disk', function () {
    expect(Upload::make())
        ->getDisk()->toBe('s3')
        ->disk('s3.key')->toBeInstanceOf(Upload::class)
        ->getDisk()->toBe('s3.key');
});

it('has max size', function () {
    expect(Upload::make())
        ->getMaxSize()->toBe(PHP_INT_MAX)
        ->max(1000)->toBeInstanceOf(Upload::class)
        ->getMaxSize()->toBe(1000);
});

it('has min size', function () {
    expect(Upload::make())
        ->getMinSize()->toBe(0)
        ->min(1000)->toBeInstanceOf(Upload::class)
        ->getMinSize()->toBe(1000);
});

it('has sizes', function () {
    expect(Upload::make())
        ->size(1000)->toBeInstanceOf(Upload::class)
        ->getMinSize()->toBe(0)
        ->getMaxSize()->toBe(1000)
        ->size(1, 10)->toBeInstanceOf(Upload::class)
        ->getMinSize()->toBe(1)
        ->getMaxSize()->toBe(10);
});

it('has unit', function () {
    expect(Upload::make())
        ->unit('petabytes')->toBeInstanceOf(Upload::class)
        ->getUnit()->toBe('petabytes')
        ->bytes()->toBeInstanceOf(Upload::class)
        ->getUnit()->toBe('bytes')
        ->kilobytes()->toBeInstanceOf(Upload::class)
        ->getUnit()->toBe('kilobytes')
        ->megabytes()->toBeInstanceOf(Upload::class)
        ->getUnit()->toBe('megabytes')
        ->gigabytes()->toBeInstanceOf(Upload::class)
        ->getUnit()->toBe('gigabytes');
});

it('has types', function () {
    expect(Upload::make())
        ->types('image/png')->toBeInstanceOf(Upload::class)
        ->getTypes()->toEqual(['image/png'])
        ->accepts('image/svg+xml')->toBeInstanceOf(Upload::class)
        ->getTypes()->toEqual(['image/svg+xml'])
        ->image()->toBeInstanceOf(Upload::class)
        ->getTypes()->toEqual(['image/'])
        ->video()->toBeInstanceOf(Upload::class)
        ->getTypes()->toEqual(['video/'])
        ->audio()->toBeInstanceOf(Upload::class)
        ->getTypes()->toEqual(['audio/']);
});

it('has duration', function () {
    $fn = fn ($d) => \sprintf('+%d seconds', $d);

    expect(Upload::make())
        ->duration(1)->toBeInstanceOf(Upload::class)
        ->getDuration()->toBe($fn(1))
        ->expires(1)->toBeInstanceOf(Upload::class)
        ->getDuration()->toBe($fn(1))
        ->seconds(1)->toBeInstanceOf(Upload::class)
        ->getDuration()->toBe($fn(1))
        ->minutes(1)->toBeInstanceOf(Upload::class)
        ->getDuration()->toBe('+1 minutes')
        ->duration('+1 hour')->toBeInstanceOf(Upload::class)
        ->getDuration()->toBe('+1 hour')
        ->duration(now()->addMinute())->toBeInstanceOf(Upload::class)
        ->getDuration()->toBe($fn(60));
});

it('has bucket', function () {
    expect(Upload::make())
        ->bucket('my-bucket')->toBeInstanceOf(Upload::class)
        ->getBucket()->toBe('my-bucket');
});

it('has acl', function () {
    expect(Upload::make())
        ->getAcl()->toBe('public-read')
        ->acl('private-read')->toBeInstanceOf(Upload::class)
        ->getAcl()->toBe('private-read');
});

it('api sample', function () {
    Upload::make()
        ->disk('avatar')
        ->size(1, 10)
        ->megabytes()
        ->path('{'.Str::uuid()->toString().'}')
        ->types('image/png');
});