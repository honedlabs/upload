<?php

declare(strict_types=1);

namespace Honed\Upload\Concerns;

use Illuminate\Support\Number;

trait BridgesSerialization
{
    /**
     * Whether the upload accepts multiple files.
     *
     * @var bool
     */
    protected $multiple = false;

    /**
     * The additional data to return with the presign response.
     *
     * @var mixed
     */
    protected $response = null;

    /**
     * Set whether the upload accepts multiple files.
     *
     * @param  bool  $multiple
     * @return $this
     */
    public function multiple($multiple = true)
    {
        $this->multiple = $multiple;

        return $this;
    }

    /**
     * Determine whether the upload accepts multiple files.
     *
     * @return bool
     */
    public function isMultiple()
    {
        return $this->multiple;
    }

    /**
     * Set the data to return with the presign response.
     *
     * @param  mixed  $response
     * @return $this
     */
    public function respondWith($response)
    {
        $this->response = $response;

        return $this;
    }

    /**
     * Get the data to return with the presign response.
     *
     * @return mixed
     */
    public function getResponse()
    {
        return $this->evaluate($this->response);
    }

    /**
     * Create the upload message.
     *
     * @param  int  $size
     * @param  array<int, string>  $extensions
     * @param  array<int, string>  $mimeTypes
     * @return string
     */
    public function getMessage($size, $extensions, $mimeTypes)
    {
        $fileTypeDescription = $this->buildFileTypeDescription($extensions, $mimeTypes);
        $maxSizeDescription = Number::fileSize($size);

        return "{$fileTypeDescription} up to {$maxSizeDescription}";
    }

    /**
     * Build a human-readable description of the accepted file types.
     *
     * @param  array<int, string>  $extensions
     * @param  array<int, string>  $mimeTypes
     */
    protected function buildFileTypeDescription($extensions, $mimeTypes): string
    {
        return match (true) {
            $this->shouldListExtensions($extensions) => $this->formatExtensions($extensions),
            $this->shouldListMimeTypes($mimeTypes) => $this->formatMimeTypes($mimeTypes),
            default => $this->getGenericFileDescription(),
        };
    }

    /**
     * Determine if extensions should be listed in the message.
     *
     * @param  array<int, string>  $extensions
     * @return bool
     */
    protected function shouldListExtensions($extensions)
    {
        return ! empty($extensions) && count($extensions) < 4;
    }

    /**
     * Determine if MIME types should be listed in the message.
     *
     * @param  array<int, string>  $mimeTypes
     * @return bool
     */
    protected function shouldListMimeTypes($mimeTypes)
    {
        return ! empty($mimeTypes) && count($mimeTypes) < 4;
    }

    /**
     * Format extensions for display in the message.
     *
     * @param  array<int, string>  $extensions
     * @return string
     */
    protected function formatExtensions($extensions)
    {
        $formattedExtensions = array_map(
            static fn (string $extension) => mb_strtoupper(trim($extension)),
            $extensions
        );

        return implode(', ', $formattedExtensions);
    }

    /**
     * Format MIME types for display in the message.
     *
     * @param  array<int, string>  $mimeTypes
     * @return string
     */
    protected function formatMimeTypes($mimeTypes)
    {
        $formattedTypes = array_map(
            static fn (string $mimeType) => trim($mimeType, ' /'),
            $mimeTypes
        );

        return ucfirst(implode(', ', $formattedTypes));
    }

    /**
     * Get a generic file description when specific types can't be listed.
     */
    protected function getGenericFileDescription(): string
    {
        return $this->isMultiple() ? 'Files' : 'A single file';
    }
}
