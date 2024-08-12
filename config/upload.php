<?php

// config for Conquest/Upload

use Illuminate\Support\Facades\Storage;

return [
    'disk' => 's3',
    'expires' => 60,
    'multiple' => false,
    'max_size' => 1024 * 1024,
    'min_size' => 0,
    'accepts' => 'image/*',
    'directory' => 'uploads',
    // 'model' => [Upload::class, 'file']
];
