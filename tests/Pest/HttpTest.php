<?php

declare(strict_types=1);

use function Pest\Laravel\post;

it('validates required fields', function () {
    post('/upload')
        ->assertInvalid([
            'name' => 'The file name field is required.',
            'type' => 'The file type field is required.',
            'size' => 'The file size field is required.',
        ]);
});
