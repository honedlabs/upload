<?php

declare(strict_types=1);

namespace Workbench\App\Uploads;

use Honed\Upload\Upload;

final class AvatarUpload extends Upload
{
    /**
     * Provide the upload with any necessary setup.
     *
     * @return void
     */
    public function setUp()
    {
        $this->max(1024 * 1024 * 2); // 2MB
    }

    /**
     * {@inheritdoc}
     */
    public function locate()
    {
        return 'avatars';
    }
}
