<?php

declare(strict_types=1);

namespace Workbench\App\Uploads;

use Honed\Upload\File;
use Honed\Upload\Upload;

class AvatarUpload extends Upload
{
    /**
     * Get the message for the upload file input.
     *
     * @return string
     */
    public function message()
    {
        return 'The avatar must be a valid image (JPEG or PNG) and less than 2MB.';
    }

    /**
     * Define the upload.
     *
     * @return $this
     */
    protected function definition(): static
    {
        return $this
            ->publicRead()
            ->maxSize(2 * 1024 * 1024)
            ->mimes(['image/jpeg', 'image/png'])
            ->extensions(['jpg', 'jpeg', 'png'])
            ->path(fn (File $file) => 'avatars/'.$file->getFilename())
            ->returning(fn (File $file) => $file->getPath());
    }
}
