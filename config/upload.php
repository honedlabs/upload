<?php

// config for Conquest/Upload

return [
    'disk' => 's3',
    'expires' => 60,
    'multiple' => false,
    'max_size' => 1024 * 1024,
    'min_size' => 0,
    'accepts' => 'image/*',
    'directory' => 'uploads',
    // 'model' => [Upload::class, 'path']
];
