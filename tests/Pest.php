<?php

declare(strict_types=1);

use Honed\Upload\Tests\TestCase;
use Illuminate\Http\Request;

uses(TestCase::class)->in(__DIR__);

function presignRequest(string $filename, string $mime, int $size): Request
{
    return Request::create('/', 'GET', [
        'name' => \pathinfo($filename, PATHINFO_FILENAME),
        'extension' => \pathinfo($filename, PATHINFO_EXTENSION),
        'type' => $mime,
        'size' => $size,
    ]);
}
