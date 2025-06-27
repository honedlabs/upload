<?php

declare(strict_types=1);

use Honed\Upload\Pipes\CreateRules;
use Honed\Upload\Upload;
use Honed\Upload\UploadRule;
use Illuminate\Http\Request;

beforeEach(function () {
    $this->pipe = new CreateRules();

    $this->upload = Upload::make();
});

it('separates request name and extension', function () {
    $request = Request::create('/', Request::METHOD_GET, [
        'name' => 'test.png',
        'type' => 'image/png',
        'size' => 1024,
    ]);

    $this->upload->request($request);

    $this->pipe->instance($this->upload)->run();

    expect($this->upload)
        ->getRule()->toBeNull()
        ->getRequest()
        ->scoped(fn ($request) => $request
            ->query('name')->toBe('test')
            ->query('extension')->toBe('png')
        );
});

it('separates request name and extension with null', function () {
    $request = Request::create('/', Request::METHOD_GET, [
        'name' => 5,
        'type' => 'image/png',
        'size' => 1024,
    ]);

    $this->upload->request($request);

    $this->pipe->instance($this->upload)->run();

    expect($this->upload)
        ->getRule()->toBeNull()
        ->getRequest()
        ->scoped(fn ($request) => $request
            ->query('name')->toBeNull()
            ->query('extension')->toBeNull()
        );
});

it('uses an upload rule', function () {
    $request = Request::create('/', Request::METHOD_GET, [
        'name' => 'test.png',
        'type' => 'image/png',
        'size' => 1024,
    ]);

    $rule = UploadRule::make()
        ->extension('png');

    $this->upload->request($request)->rule($rule);

    $this->pipe->instance($this->upload)->run();

    expect($this->upload)
        ->getRule()->toBe($rule)
        ->getRequest()
        ->scoped(fn ($request) => $request
            ->query('name')->toBe('test')
            ->query('extension')->toBe('png')
        );
});
