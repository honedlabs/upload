<?php

declare(strict_types=1);

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Workbench\App\Uploads\AvatarUpload;

beforeEach(function () {
    $this->upload = AvatarUpload::make();

    $this->upload->define();
});

it('has definition', function () {
    expect($this->upload)
        ->getMinSize()->toBe(0)
        ->getMaxSize()->toBe(2 * 1024 * 1024)
        ->getMimeTypes()->toEqual(['image/jpeg', 'image/png'])
        ->getExtensions()->toEqual(['jpg', 'jpeg', 'png'])
        ->getPolicy()->toBe('public-read')
        ->getPathCallback()->toBeInstanceOf(Closure::class);
});

it('creates upload', function () {
    $request = Request::create('/', Request::METHOD_GET, [
        'name' => 'test.png',
        'type' => 'image/png',
        'size' => 1024,
    ]);

    expect($this->upload->toResponse($request))
        ->toBeInstanceOf(JsonResponse::class)
        ->getData()->{'data'}->toBe('avatars/test.png');
});

it('has message', function () {
    expect($this->upload)
        ->message()->toBe('The avatar must be a valid image (JPEG or PNG) and less than 2MB.');
});
