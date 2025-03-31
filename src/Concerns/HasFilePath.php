<?php

declare(strict_types=1);

namespace Honed\Upload\Concerns;

use Honed\Upload\Contracts\ShouldAnonymize;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HasFilePath
{
    /**
     * The disk to retrieve the path credentials from.
     *
     * @var string|null
     */
    protected $disk;

    /**
     * The path prefix to store the file in
     *
     * @var string|\Closure(mixed...):string|null
     */
    protected $path;

    /**
     * The name of the file to be stored.
     *
     * @var string|\Closure(mixed...):string|null
     */
    protected $name;

    /**
     * Whether the file name should be generated using a UUID.
     *
     * @var bool|null
     */
    protected $anonymize;

    /**
     * Set the disk to retrieve the S3 credentials from.
     *
     * @param  string|null  $disk
     * @return $this
     */
    public function disk($disk)
    {
        if (filled($disk)) {
            $this->disk = $disk;
        }

        return $this;
    }

    /**
     * Get the S3 disk to use for uploading files.
     *
     * @return string
     */
    public function getDisk()
    {
        return $this->disk ?? static::getDefaultDisk();
    }

    /**
     * Get the disk to use for uploading files from the config.
     *
     * @return string
     */
    public static function getDefaultDisk()
    {
        return type(config('upload.disk', 's3'))->asString();
    }

    /**
     * Set the path to store the file at.
     *
     * @param  string|\Closure(mixed...):string  $path
     * @return $this
     */
    public function path($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get the path to store the file at.
     *
     * @return string|\Closure(mixed...):string|null
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set the name, or method, of generating the name of the file to be stored.
     *
     * @param  \Closure(mixed...):string|string  $name
     * @return $this
     */
    public function name($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the name, or method, of generating the name of the file to be stored.
     *
     * @return \Closure(mixed...):string|string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set whether to anonymize the file name using a UUID.
     *
     * @param  bool  $anonymize
     * @return $this
     */
    public function anonymize($anonymize = true)
    {
        $this->anonymize = $anonymize;

        return $this;
    }

    /**
     * Determine whether the file name should be anonymized using a UUID.
     *
     * @return bool
     */
    public function isAnonymized()
    {
        if (isset($this->anonymize)) {
            return $this->anonymize;
        }

        if ($this instanceof ShouldAnonymize) {
            return true;
        }

        return $this->isAnonymizedByDefault();
    }

    /**
     * Determine whether the file name should be anonymized using a UUID by default.
     *
     * @return bool
     */
    public function isAnonymizedByDefault()
    {
        return (bool) config('upload.anonymize', false);
    }

    /**
     * Get the S3 client to use for uploading files.
     *
     * @return \Aws\S3\S3Client
     */
    public function getClient()
    {
        $disk = $this->getDisk();

        /** @var \Illuminate\Filesystem\AwsS3V3Adapter */
        $client = Storage::disk($disk);

        return $client->getClient();
    }

    /**
     * Get the complete filename of the file to be stored.
     *
     * @param  \Honed\Upload\UploadData  $data
     * @return string
     */
    public function createFilename($data)
    {
        return once(function () use ($data) {
            $name = $this->getName();

            if ($this->isAnonymized()) {
                return Str::uuid()->toString();
            }

            if (isset($name)) {
                return $this->evaluate($name);
            }

            return $data->name;
        });
    }

    /**
     * Build the storage key path for the uploaded file.
     *
     * @param  \Honed\Upload\UploadData  $data
     * @return string
     */
    public function createKey($data)
    {
        return once(function () use ($data) {
            $filename = $this->createFilename($data);

            $path = $this->evaluate($this->getPath());

            return Str::of($filename)
                ->append('.', $data->extension)
                ->when($path, fn ($name, $path) => $name
                    ->prepend($path, '/')
                    ->replace('//', '/'),
                )->trim('/')
                ->value();
        });
    }

    /**
     * Get the immediate folder from a file path, if it exists.
     *
     * @param  string  $path
     * @return string|null
     */
    public static function getFolder($path)
    {
        return Str::of($path)
            ->explode('/')
            ->filter()
            ->slice(0, -1)
            ->last();
    }

    /**
     * Destructure the filename into its components.
     *
     * @param  mixed  $filename
     * @return ($filename is string ? array{string, string} : array{null, null})
     */
    public static function destructureFilename($filename)
    {
        if (! \is_string($filename)) {
            return [null, null];
        }

        return [
            \pathinfo($filename, PATHINFO_FILENAME),
            \mb_strtolower(\pathinfo($filename, PATHINFO_EXTENSION)),
        ];
    }
}
