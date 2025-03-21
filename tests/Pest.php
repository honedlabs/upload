<?php

declare(strict_types=1);

use Honed\Upload\Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as RequestFacade;

uses(TestCase::class)->in(__DIR__);

function presignRequest(string $filename, string $mime, int $size): Request
{
    return RequestFacade::create('/', 'GET', [
        'name' => $filename,
        'type' => $mime,
        'size' => $size,
    ]);
}
