<?php

declare(strict_types=1);

namespace Honed\Upload\Concerns;

use Illuminate\Support\Arr;
use Illuminate\Support\Number;

trait ValidatesUpload
{
    /**
     * The expiry duration of the request in seconds.
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
     * List of the file mime types and extensions.
     *
     * @var array<int, string>
     */
    protected $types = [];

    /**
     * Set the expiry duration of the request in seconds.
     *
     * @param  int  $seconds
     * @return $this
     */
    public function expiresIn($seconds)
    {
        $this->expires = $seconds;

        return $this;
    }

    /**
     * Get the expiry duration of the request in seconds.
     *
     * @return int
     */
    public function getExpiry()
    {
        return $this->expires ?? static::getDefaultExpiry();
    }

    /**
     * Get the default expiry duration of the request in seconds.
     *
     * @return int
     */
    public static function getDefaultExpiry()
    {
        return type(config('upload.expires', 120))->asInt();
    }

    /**
     * Format the expiry duration of the request.
     *
     * @return string
     */
    public function formatExpiry()
    {
        return \sprintf('+%d seconds', \abs($this->getExpiry()));
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
        return type(config('upload.min_size', 0))->asInt();
    }

    /**
     * Set the file mime types and extensions.
     *
     * @param  string|iterable<int,string>  ...$types
     * @return $this
     */
    public function types(...$types)
    {
        $types = Arr::flatten($types);

        $this->types = \array_merge($this->types, $types);

        return $this;
    }

    /**
     * Set the upload to only accept images.
     *
     * @return $this
     */
    public function onlyImages()
    {
        return $this->types('image/');
    }

    /**
     * Set the upload to only accept videos.
     *
     * @return $this
     */
    public function onlyVideos()
    {
        return $this->types('video/');
    }

    /**
     * Set the upload to only accept audio.
     *
     * @return $this
     */
    public function onlyAudio()
    {
        return $this->types('audio/');
    }

    /**
     * Set the upload to only accept PDF files.
     *
     * @return $this
     */
    public function onlyPdf()
    {
        return $this->types('application/pdf', '.pdf');
    }

    /**
     * Get the accepted file mime types and extensions.
     *
     * @return array<int, string>
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * Get the file mime types.
     *
     * @return array<int, string>
     */
    public function getMimes()
    {
        return \array_values(
            \array_filter(
                $this->getTypes(),
                static fn ($type) => ! \str_starts_with($type, '.')
            )
        );
    }

    /**
     * Get the file extensions.
     *
     * @return array<int, string>
     */
    public function getExtensions()
    {
        return \array_values(
            \array_filter(
                $this->getTypes(),
                static fn ($type) => \str_starts_with($type, '.')
            )
        );
    }

    /**
     * Create the general rules for validating the presigned POST request.
     *
     * @return array<string, mixed>
     */
    public function createRules()
    {
        return [
            'name' => ['required', 'string', 'max:1024'],
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
                    $min = $this->getMin();

                    if ($value < $min) {
                        $fail(__('upload::messages.min_size', [
                            'size' => Number::fileSize($min),
                        ]));
                    }

                    $max = $this->getMax();

                    if ($value > $max) {
                        $fail(__('upload::messages.max_size', [
                            'size' => Number::fileSize($max),
                        ]));
                    }
                },
            ],
            'type' => [
                'required',
                'string',
                function (string $attribute, string $value, \Closure $fail) {
                    $types = $this->getMimes();

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
