<?php

declare(strict_types=1);

use Honed\Upload\UploadData;
use Illuminate\Support\Facades\Event;
use Honed\Upload\Events\PresignFailed;
use Honed\Upload\Events\PresignCreated;
use Honed\Upload\Concerns\DispatchesPresignEvents;

beforeEach(function () {
    $this->test = new class {
        use DispatchesPresignEvents;
    };

    Event::fake();
});

it('dispatches created presign event', function () {
    $data = UploadData::from([
        'name' => 'test',
        'extension' => 'txt',
        'type' => 'text/plain',
        'size' => 100,
        'meta' => null,
    ]);

    $this->test->createdPresign($data, 's3');

    Event::assertDispatched(PresignCreated::class);
});

it('dispatches failed presign event', function () {
    $this->test->failedPresign(request());

    Event::assertDispatched(PresignFailed::class);
});
