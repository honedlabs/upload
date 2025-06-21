<?php

declare(strict_types=1);

use Honed\Upload\Upload;
use Honed\Upload\UploadRule;

beforeEach(function () {
    $this->upload = Upload::make();
});

it('adds rules', function () {
    expect($this->upload)
        ->getRules()->toBeEmpty()
        ->rules([UploadRule::make()])->toBe($this->upload)
        ->getRules()->toHaveCount(1)
        ->rules(UploadRule::make())->toBe($this->upload)
        ->getRules()->toHaveCount(2);
});

it('adds a rule', function () {
    expect($this->upload)
        ->getRules()->toBeEmpty()
        ->rule(UploadRule::make())->toBe($this->upload)
        ->getRules()->toHaveCount(1);
});

it('sets active rule', function () {

    expect($this->upload)
        ->getRule()->toBeNull()
        ->setRule(UploadRule::make())->toBeNull()
        ->getRule()->toBeInstanceOf(UploadRule::class);
});
