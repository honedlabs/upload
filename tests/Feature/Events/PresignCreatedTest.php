<?php

declare(strict_types=1);

use Honed\Upload\Events\PresignCreated;
use Honed\Upload\File;
use Honed\Upload\Upload;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    Event::fake();
});

it('dispatches event', function () {
    $upload = Upload::make();
    $file = new File();

    PresignCreated::dispatch($upload::class, $file, 's3');

    Event::assertDispatched(PresignCreated::class);
});
