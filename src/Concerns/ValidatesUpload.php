<?php

declare(strict_types=1);

namespace Honed\Upload\Concerns;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Number;

use function abs;
use function array_map;
use function array_merge;
use function implode;
use function in_array;
use function mb_strtolower;
use function sprintf;
use function str_starts_with;
use function trim;

trait ValidatesUpload
{
    /**
     * The lifetime of the request in minutes.
     *
     * @var int
     */
    protected $lifetime = 2;

    /**
     * The maximum file size in bytes.
     *
     * @var int
     */
    protected $maxSize = 2147483647;

    /**
     * The minimum file size in bytes.
     *
     * @var int
     */
    protected $minSize = 0;

    /**
     * The accepted file mime types.
     *
     * @var array<int, string>
     */
    protected $mimes = [];

    /**
     * The accepted file extensions.
     *
     * @var array<int, string>
     */
    protected $extensions = [];

    /**
     * Set the expiry duration of the request in minutes.
     *
     * @param  int  $minutes
     * @return $this
     */
    public function lifetime($minutes)
    {
        $this->lifetime = $minutes;

        return $this;
    }

    /**
     * Get the lifetime of the request in minutes.
     *
     * @return int
     */
    public function getLifetime()
    {
        return $this->lifetime;
    }

    /**
     * Format the lifetime of the request.
     *
     * @param  int  $minutes
     * @return string
     */
    public function formatLifetime($minutes)
    {
        return sprintf('+%d minutes', abs($minutes));
    }

    /**
     * Set the maximum file size in bytes.
     *
     * @param  int  $bytes
     * @return $this
     */
    public function maxSize($bytes)
    {
        $this->maxSize = $bytes;

        return $this;
    }

    /**
     * Get the maximum file size in bytes.
     *
     * @return int
     */
    public function getMaxSize()
    {
        return $this->maxSize;
    }

    /**
     * Set the minimum file size in bytes.
     *
     * @param  int  $bytes
     * @return $this
     */
    public function minSize($bytes)
    {
        $this->minSize = $bytes;

        return $this;
    }

    /**
     * Get the minimum file size in bytes.
     *
     * @return int
     */
    public function getMinSize()
    {
        return $this->minSize;
    }

    /**
     * Merge a set of accepted mime types with the existing.
     *
     * @param  string|array<int,string>  $types
     * @return $this
     */
    public function mimes($types)
    {
        /** @var array<int, string> */
        $types = is_array($types) ? $types : func_get_args();

        $types = array_map(
            static fn ($type) => rtrim(mb_strtolower(trim($type, ' *')), '/'),
            $types
        );

        $this->mimes = [...$this->mimes, ...$types];

        return $this;
    }

    /**
     * Merge a set of accepted mime types with the existing.
     *
     * @param  string|array<int,string>  $types
     * @return $this
     */
    public function mimeTypes($types)
    {
        return $this->mimes($types);
    }

    /** 
     * Add a mime type to the accepted mime types.
     *
     * @param  string  $type
     * @return $this
     */
    public function mime($type)
    {
        return $this->mimes($type);
    }

    /**
     * Add a mime type to the accepted mime types.
     *
     * @param  string  $type
     * @return $this
     */
    public function mimeType($type)
    {
        return $this->mime($type);
    }

    /**
     * Get the accepted file mime types.
     *
     * @return array<int, string>
     */
    public function getMimeTypes()
    {
        return $this->mimes;
    }

    /**
     * Merge a set of accepted extensions with the existing.
     *
     * @param  string|array<int,string>  $extensions
     * @return $this
     */
    public function extensions($extensions)
    {
        /** @var array<int, string> */
        $extensions = is_array($extensions) ? $extensions : func_get_args();

        $extensions = array_map(
            static fn ($ext) => mb_strtolower(trim($ext, ' .')),
            $extensions
        );

        $this->extensions = [...$this->extensions, ...$extensions];

        return $this;
    }

    /**
     * Add an accepted file extension.
     *
     * @param  string  $extension
     * @return $this
     */
    public function extension($extension)
    {
        return $this->extensions($extension);
    }

    /**
     * Get the accepted file extensions.
     *
     * @return array<int, string>
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * Create the general rules for validating the presigned POST request.
     *
     * @return array<string, mixed>
     */
    public function createRules()
    {
        return [
            'name' => [
                'required',
                'string',
                'max:1024',
            ],
            'extension' => [
                'required',
                'string',
                function (string $attribute, string $value, Closure $fail) {
                    $extensions = $this->getExtensions();

                    if (! filled($extensions)) {
                        return;
                    }

                    if (! in_array($value, $extensions)) {
                        $fail(__('upload::messages.extension', [
                            'extensions' => implode(', ', $extensions),
                        ]));
                    }
                },
            ],
            'size' => [
                'required',
                'integer',
                function (string $attribute, int $value, Closure $fail) {
                    $max = $this->getMaxSize();

                    if ($value > $max) {
                        $fail(__('upload::messages.max_size', [
                            'size' => Number::fileSize($max),
                        ]));
                    }

                    $min = $this->getMinSize();

                    if ($value < $min) {
                        $fail(__('upload::messages.min_size', [
                            'size' => Number::fileSize($min),
                        ]));
                    }
                },
            ],
            'type' => [
                'required',
                'string',
                function (string $attribute, string $value, Closure $fail) {
                    $types = $this->getMimeTypes();

                    if (! filled($types)) {
                        return;
                    }

                    foreach ($types as $type) {
                        if (str_starts_with($value, $type)) {
                            return;
                        }
                    }

                    $fail(__('upload::messages.type'));
                },
            ],
            'meta' => ['nullable'],
        ];
    }
}
