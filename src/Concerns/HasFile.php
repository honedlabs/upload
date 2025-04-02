<?php

declare(strict_types=1);

namespace Honed\Upload\Concerns;

use Honed\Upload\Contracts\ShouldAnonymize;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HasFile
{
    /**
     * The disk to retrieve the location credentials from.
     *
     * @var string|null
     */
    protected $disk;

    /**
     * The location prefix to store the file in
     *
     * @var string|\Closure(mixed...):string|null
     */
    protected $location;

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
     * Set the location to store the file at.
     *
     * @param  string|\Closure(mixed...):string  $location
     * @return $this
     */
    public function location($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Define the location to store the file at.
     *
     * @return string|\Closure(mixed...):string|null
     */
    public function locate()
    {
        return null;
    }

    /**
     * Get the location to store the file at.
     *
     * @return string|\Closure(mixed...):string|null
     */
    public function getLocation()
    {
        if (isset($this->location)) {
            return $this->evaluate($this->location);
        }

        return $this->locate();
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
     * Build the storage key location for the uploaded file.
     *
     * @param  \Honed\Upload\UploadData  $data
     * @return string
     */
    public function createKey($data)
    {
        return once(function () use ($data) {
            $filename = $this->createFilename($data);

            $location = $this->evaluate($this->getLocation());

            return Str::of($filename)
                ->append('.', $data->extension)
                ->when($location, fn ($name, $location) => $name
                    ->prepend($location, '/')
                    ->replace('//', '/'),
                )->trim('/')
                ->value();
        });
    }

    /**
     * Get the immediate folder from a file location, if it exists.
     *
     * @param  string  $location
     * @return string|null
     */
    public static function getFolder($location)
    {
        return Str::of($location)
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
