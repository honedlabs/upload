<?php

declare(strict_types=1);

use Aws\S3\S3Client;
use Honed\Upload\Upload;

beforeEach(function () {
    $this->upload = Upload::make();
});

it('sets policy', function () {
    expect($this->upload)
        ->getPolicy()->toBe('private')
        ->policy('public')->toBe($this->upload)
        ->getPolicy()->toBe('public')
        ->publicRead()->toBe($this->upload)
        ->getPolicy()->toBe('public-read')
        ->private()->toBe($this->upload)
        ->getPolicy()->toBe('private');
});

it('sets disk', function () {
    expect($this->upload)
        ->getDisk()->toBe('s3')
        ->disk('r2')->toBe($this->upload)
        ->getDisk()->toBe('r2');
});

it('sets bucket', function () {
    expect($this->upload)
        ->getBucket()->toBe('test')
        ->bucket('cdn')->toBe($this->upload)
        ->getBucket()->toBe('cdn');
});

it('gets form inputs', function () {
    expect($this->upload)
        ->getFormInputs('test')->toBe([
            'acl' => 'private',
            'key' => 'test',
        ]);
});

it('gets options', function () {
    expect($this->upload)
        ->getOptions('test', 'image/jpeg', 100)->toBe([
            ['eq', '$acl', 'private'],
            ['eq', '$key', 'test'],
            ['eq', '$bucket', 'test'],
            ['content-length-range', 100, 100],
            ['eq', '$Content-Type', 'image/jpeg'],
        ]);
});

it('has presign', function () {
    expect($this->upload)
        ->getPresign()->toBeNull()
        ->setPresign(null)->toBeNull();
});

it('gets client', function () {
    expect($this->upload)
        ->getClient()->toBeInstanceOf(S3Client::class);
});
