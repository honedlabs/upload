<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Storage Disk
    |--------------------------------------------------------------------------
    |
    | Specify the storage disk on your filesystems.php config file to be used
    | for your uploads if no disk is provided at runtime to the Upload class.
    | This is also used to resolve the bucket name if no bucket is provided at
    | runtime to the Upload class, or below.
    */

    'disk' => 's3',

    /*
    |--------------------------------------------------------------------------
    | File sizes
    |--------------------------------------------------------------------------
    |
    | You can globally specify the minimum and maximum file sizes for your
    | uploads. This will be used to validate the file size of the uploaded
    | file, both when making a request to an upload endpoint and as a
    | policy in S3.
    |
    | The make it simpler, you can also specify the units you are using as a
    | string, such as 'megabytes'.
    */

    'min_size' => 1,
    'max_size' => 10 * (1024 ** 3), // 10GB

    /*
    |--------------------------------------------------------------------------
    | Request duration
    |--------------------------------------------------------------------------
    |
    | You can globally specify the expiration time for your upload requests in
    | minutes. This will be used to validate the expiration time of the request
    | to the upload endpoint.
    */

    'expires' => 2,

    /*
    |--------------------------------------------------------------------------
    | ACL
    |--------------------------------------------------------------------------
    |
    | You can globally specify the ACL for your uploads. This will be used to
    | set the ACL for the uploaded file. Typically you will not need to change
    | this, but you can if you need to.
    */

    'acl' => 'public-read',

    /*
    |--------------------------------------------------------------------------
    | Anonymize
    |--------------------------------------------------------------------------
    |
    | You can globally enable all files to be anonymized. This will redact the
    | given filename and replace it with a UUID.
    */

    'anonymize' => false,

];
