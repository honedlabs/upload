<?php

declare(strict_types=1);

namespace Honed\Upload;

use Illuminate\Support\Arr;
use Honed\Upload\Exceptions\PathNotSetException;

class File
{
    /**
     * The name of the file.
     * 
     * @var string
     */
    public $name;
    
    /**
     * The extension of the file.
     * 
     * @var string
     */
    public $extension;

    /**
     * The MIME type of the file.
     * 
     * @var string
     */
    public $mimeType;
    
    /**
     * The size of the file in bytes.
     * 
     * @var int
     */
    public $size;

    /**
     * The meta data of the file.
     * 
     * @var mixed
     */
    public $meta;

    /**
     * The path of the file in storage.
     * 
     * @var string
     */
    public $path;

    /**
     * Create a new upload data instance from the validated data.
     *
     * @param  array{name:string,extension:string,type:string,size:int,meta:mixed}  $data
     * @return static
     */
    public static function from($data)
    {
        $name = $data['name'];
        $extension = $data['extension'];

        return resolve(static::class)
            ->name($name)
            ->extension($extension)
            ->mimeType($data['type'])
            ->size($data['size'])
            ->meta($data['meta'])
            ->path($name.'.'.$extension);
    }

    /**
     * Set the name of the file.
     *
     * @param  string  $name
     * @return $this
     */
    public function name($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the name of the file.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the extension of the file.
     *
     * @param  string  $extension
     * @return $this
     */
    public function extension($extension)
    {
        $this->extension = $extension;

        return $this;
    }

    /**
     * Get the extension of the file.
     *
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * Get the filename of the file.
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->name . '.' . $this->extension;
    }

    /**
     * Set the MIME type of the file.
     *
     * @param  string  $mimeType
     * @return $this
     */
    public function mimeType($mimeType)
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    /**
     * Get the MIME type of the file.
     *
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * Set the size of the file.
     *
     * @param  int  $size
     * @return $this
     */
    public function size($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Get the size of the file.
     *
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set the meta data of the file.
     *
     * @param  mixed  $meta
     * @return $this
     */
    public function meta($meta)
    {
        $this->meta = $meta;

        return $this;
    }

    /**
     * Get the meta data of the file.
     *
     * @return mixed
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * Set the path of the file using the properties.
     * 
     * @return void
     */
    public function setPath()
    {
        $this->path = $this->getFilename();
    }

    /**
     * Set the path of the file.
     *
     * @param  string  $path
     * @return $this
     */
    public function path($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get the path of the file.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }
}
