<?php

declare(strict_types=1);

use Honed\Upload\UploadRule;

it('makes', function () {
    expect(UploadRule::make())
        ->toBeInstanceOf(UploadRule::class);
});

it('matches', function () {
    expect(UploadRule::make()->mimes('video')->extensions('MP4'))
        ->isMatching('image/png', 'png')->toBeFalse()
        ->isMatching('video/mp4', 'png')->toBeTrue()
        ->isMatching('image/png', 'mp4')->toBeTrue();
});
