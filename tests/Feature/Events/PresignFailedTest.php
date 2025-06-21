<?php

declare(strict_types=1);

use Honed\Upload\Events\PresignFailed;
use Honed\Upload\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    Event::fake();
});

it('dispatches event', function () {
    $upload = Upload::make();

    PresignFailed::dispatch($upload::class, Request::create('/'));

    Event::assertDispatched(PresignFailed::class);
});
