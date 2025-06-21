<?php

declare(strict_types=1);

use Honed\Upload\UploadRule;

beforeEach(function () {
    $this->rule = UploadRule::make();
});

it('matches', function () {
    expect($this->rule->mimes('video')->extensions('MP4'))
        ->isMatching('image/png', 'png')->toBeFalse()
        ->isMatching('video/mp4', 'png')->toBeTrue()
        ->isMatching('image/png', 'mp4')->toBeTrue();
});
