<?php

declare(strict_types=1);

namespace Honed\Upload\Concerns;

use Illuminate\Support\Arr;
use Illuminate\Support\Number;

trait ValidatesUpload
{
    /**
     * The expiry duration of the request in minutes.
     *
     * @var int|null
     */
    protected $expires;

    /**
     * The maximum file size in bytes.
     *
     * @var int|null
     */
    protected $max;

    /**
     * The minimum file size in bytes.
     *
     * @var int|null
     */
    protected $min;

    /**
     * The accepted file mime types.
     *
     * @var array<int, string>
     */
    protected $mimeTypes = [];

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
    public function expiresIn($minutes)
    {
        $this->expires = $minutes;

        return $this;
    }

    /**
     * Get the expiry duration of the request in minutes.
     *
     * @return int
     */
    public function getExpiry()
    {
        return $this->expires ?? static::getDefaultExpiry();
    }

    /**
     * Get the default expiry duration of the request in minutes.
     *
     * @return int
     */
    public static function getDefaultExpiry()
    {
        return type(config('upload.expires', 2))->asInt();
    }

    /**
     * Format the expiry duration of the request.
     *
     * @return string
     */
    public function formatExpiry()
    {
        return \sprintf('+%d minutes', \abs($this->getExpiry()));
    }

    /**
     * Set the maximum file size in bytes.
     *
     * @param  int  $size
     * @return $this
     */
    public function max($size)
    {
        $this->max = $size;

        return $this;
    }

    /**
     * Get the maximum file size in bytes.
     *
     * @return int
     */
    public function getMax()
    {
        return $this->max ?? static::getDefaultMax();
    }

    /**
     * Get the default maximum file size in bytes.
     *
     * @return int
     */
    public static function getDefaultMax()
    {
        return type(config('upload.max_size', 10 * (1024 ** 3)))->asInt();
    }

    /**
     * Set the minimum file size in bytes.
     *
     * @param  int  $size
     * @return $this
     */
    public function min($size)
    {
        $this->min = $size;

        return $this;
    }

    /**
     * Get the minimum file size in bytes.
     *
     * @return int
     */
    public function getMin()
    {
        return $this->min ?? static::getDefaultMin();
    }

    /**
     * Get the default minimum file size in bytes.
     *
     * @return int
     */
    public static function getDefaultMin()
    {
        return type(config('upload.min_size', 1))->asInt();
    }

    /**
     * Set the accepted file mime types.
     *
     * @param  string|iterable<int,string>  ...$types
     * @return $this
     */
    public function mimes(...$types)
    {
        $types = \array_map(
            static fn ($type) => rtrim(\mb_strtolower(\trim($type, ' *')), '/').'/',
            Arr::flatten($types)
        );

        $this->mimeTypes = \array_merge($this->mimeTypes, $types);

        return $this;
    }

    /**
     * Get the accepted file mime types.
     *
     * @return array<int, string>
     */
    public function getMimeTypes()
    {
        return $this->mimeTypes;
    }

    /**
     * Set the accepted file extensions.
     *
     * @param  string|iterable<int,string>  ...$extensions
     * @return $this
     */
    public function extensions(...$extensions)
    {
        $extensions = \array_map(
            static fn ($ext) => \mb_strtolower(\trim($ext, ' .')),
            Arr::flatten($extensions)
        );

        $this->extensions = \array_merge($this->extensions, $extensions);

        return $this;
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
                function (string $attribute, string $value, \Closure $fail) {
                    $extensions = $this->getExtensions();

                    if (! filled($extensions)) {
                        return;
                    }

                    if (! \in_array($value, $extensions)) {
                        $fail(__('upload::messages.extension', [
                            'extensions' => \implode(', ', $extensions),
                        ]));
                    }
                },
            ],
            'size' => [
                'required',
                'integer',
                function (string $attribute, int $value, \Closure $fail) {
                    $max = $this->getMax();

                    if ($value > $max) {
                        $fail(__('upload::messages.max_size', [
                            'size' => Number::fileSize($max),
                        ]));
                    }

                    $min = $this->getMin();

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
                function (string $attribute, string $value, \Closure $fail) {
                    $types = $this->getMimeTypes();

                    if (! filled($types)) {
                        return;
                    }

                    foreach ($types as $type) {
                        if (\str_starts_with($value, $type)) {
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
