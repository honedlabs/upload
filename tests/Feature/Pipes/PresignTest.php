<?php

declare(strict_types=1);

use Aws\S3\PostObjectV4;
use Honed\Upload\Events\PresignCreated;
use Honed\Upload\File;
use Honed\Upload\Pipes\Presign;
use Honed\Upload\Upload;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    $this->pipe = new Presign();

    $this->upload = Upload::make();

    $this->upload->setFile(File::from([
        'name' => 'test.png',
        'extension' => 'png',
        'type' => 'image/png',
        'size' => 1024,
    ]));

    Event::fake();
});

it('creates presign', function () {
    $this->pipe->run($this->upload);

    expect($this->upload)
        ->getPresign()->toBeInstanceOf(PostObjectV4::class);

    Event::assertDispatched(PresignCreated::class);
});
