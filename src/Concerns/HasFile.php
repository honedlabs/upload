<?php

declare(strict_types=1);

namespace Honed\Upload\Concerns;

use Closure;
use function pathinfo;
use function is_string;
use function mb_strtolower;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Honed\Upload\Contracts\ShouldBeUuid;
use Honed\Upload\Contracts\ShouldAnonymize;
use Honed\Upload\Exceptions\FileNotSetException;

trait HasFile
{
    /**
     * The name of the file to be stored.
     *
     * @var string|Closure(mixed...):string|null
     */
    protected $name;

    /**
     * Whether the file name should be a UUID.
     *
     * @var bool
     */
    protected $uuid = false;

    /**
     * A handler to create the file path.
     * 
     * @var (\Closure(mixed...):string)|null
     */
    protected $path;

    /**
     * The file data transfer object.
     * 
     * @var \Honed\Upload\File|null
     */
    protected $file;

    /**
     * Set the name, or method, of generating the name of the file to be stored.
     *
     * @param  Closure(mixed...):string|string  $name
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
     * @return Closure(mixed...):string|string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the file name of the upload to be a UUID.
     * 
     * @param  bool  $uuid
     * @return $this
     */
    public function uuid($uuid = true)
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * Determine if the file name should be a UUID.
     *
     * @return bool
     */
    public function isUuid()
    {
        return $this->uuid || $this instanceof ShouldBeUuid;
    }

    /**
     * Set the path to the file.
     *
     * @param  \Closure(mixed...):string  $path
     * @return $this
     */
    public function path($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get the path callback.
     *
     * @return (\Closure(mixed...):string)|null
     */
    public function getPathCallback()
    {
        return $this->path;
    }

    /**
     * Set the file data and evaluate the callbacks.
     *
     * @param  \Honed\Upload\File  $file
     * @return void
     */
    public function setFile($file)
    {
        $this->file = $file;

        /** @var string $name */
        $name = match (true) {
            (bool) $n = $this->getName() => $this->evaluate($n),
            $this->isUuid() => Str::uuid()->toString(),
            default => $this->file->getName(),
        };

        $this->file->name($name);

        $this->file->setPath();

        /** @var string|null $path */
        $path = $this->evaluate($this->path);

        if ($path) {
            $this->file->path($path);
        }
    }

    /**
     * Get the file data transfer object.
     *
     * @return \Honed\Upload\File
     * 
     * @throws \Honed\Upload\Exceptions\FileNotSetException
     */
    public function getFile()
    {
        if (! $this->file) {
            FileNotSetException::throw();
        }

        return $this->file;
    }
}
