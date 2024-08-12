<?php

namespace Conquest\Upload\Http\Controllers;

use Illuminate\Http\Testing\MimeType;

class PresignController
{

    public function __invoke(PresignRequest $request)
    {
        $presigned = Presign::make(UploadData::from($request));
    }
}