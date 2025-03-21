<?php

declare(strict_types=1);

use Honed\Upload\UploadData;
use Illuminate\Support\Facades\Request;

it('creates from request', function () {
    $request = Request::create('/', 'POST', [
        'name' => 'test',
        'extension' => 'png',
        'type' => 'image/png',
        'size' => 1024,
        'meta' => ['publisher' => 10],
    ]);

    expect(UploadData::from($request->all()))
        ->toBeInstanceOf(UploadData::class)
        ->name->toBe('test')
        ->extension->toBe('png')
        ->type->toBe('image/png')
        ->size->toBe(1024)
        ->meta->toBe(['publisher' => 10]);
});