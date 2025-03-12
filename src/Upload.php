<?php

declare(strict_types=1);

namespace Honed\Upload;

use Aws\S3\PostObjectV4;
use Aws\S3\S3Client;
use Carbon\Carbon;
use Honed\Core\Concerns\HasRequest;
use Honed\Core\Primitive;
use Honed\Upload\Rules\OfType;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Support\Traits\Tappable;

/**
 * @extends \Honed\Core\Primitive<string,mixed>
 */
class Upload extends Primitive
{
    use Conditionable;
    use HasRequest;
    use Macroable;
    use Tappable;

    /**
     * The disk to retrieve the S3 credentials from.
     *
     * @var string|null
     */
    protected $disk;

    /**
     * The maximum file size to upload.
     *
     * @var int|null
     */
    protected $maxSize;

    /**
     * The minimum file size to upload.
     *
     * @var int|null
     */
    protected $minSize;

    /**
     * The file size unit to use.
     *
     * @var 'bytes'|'kilobytes'|'megabytes'|'gigabytes'|string|null
     */
    protected $unit;

    /**
     * The types of files to accept.
     *
     * @var array<string>
     */
    protected $accepts = [];

    /**
     * The duration of the presigned URL.
     *
     * @var \Carbon\Carbon|int|string|null
     */
    protected $duration;

    /**
     * The path prefix to store the file in
     *
     * @var string|\Closure|null
     */
    protected $path;

    /**
     * The name of the file to be stored.
     *
     * @var string|\Closure|null
     */
    protected $name;

    /**
     * The access control list to use for the file.
     *
     * @var string|null
     */
    protected $acl;

    /**
     * Set the file upload component to accept multiple files.
     *
     * @var bool
     */
    protected $multiple = false;

    /**
     * Create a new upload instance.
     */
    public function __construct(Request $request)
    {
        $this->request($request);
        parent::__construct();
    }

    /**
     * Create a new upload instance.
     *
     * @return \Honed\Upload\Upload
     */
    public static function make()
    {
        return resolve(static::class);
    }

    /**
     * Set the disk to retrieve the S3 credentials from.
     *
     * @param  string  $disk
     * @return $this
     */
    public function disk($disk)
    {
        $this->disk = $disk;

        return $this;
    }

    /**
     * Get the S3 disk to use for uploading files.
     *
     * @return string
     */
    public function getDisk()
    {
        if (isset($this->disk)) {
            return $this->disk;
        }

        return static::fallbackDisk();
    }

    /**
     * Get the disk to use for uploading files from the config.
     *
     * @default 's3'
     *
     * @return string
     */
    public static function fallbackDisk()
    {
        return type(config('upload.disk', 's3'))->asString();
    }

    /**
     * Set the maximum file size to upload.
     *
     * @param  int  $max
     * @return $this
     */
    public function max($max)
    {
        $this->maxSize = $max;

        return $this;
    }

    /**
     * Set the minimum file size to upload.
     *
     * @param  int  $min
     * @return $this
     */
    public function min($min)
    {
        $this->minSize = $min;

        return $this;
    }

    /**
     * Set the minimum and maximum file size to upload.
     *
     * @param  int  $size
     * @param  int|null  $max
     * @return $this
     */
    public function size($size, $max = null)
    {
        return $this->when(\is_null($max),
            fn () => $this->max($size),
            fn () => $this->min($size)->max(type($max)->asInt()),
        );
    }

    /**
     * Set the file size unit to use.
     *
     * @param  'bytes'|'kilobytes'|'megabytes'|'gigabytes'|string  $unit
     * @return $this
     */
    public function unit($unit)
    {
        $this->unit = \mb_strtolower($unit);

        return $this;
    }

    /**
     * Set the file size unit to bytes.
     *
     * @return $this
     */
    public function bytes()
    {
        return $this->unit('bytes');
    }

    /**
     * Set the file size unit to kilobytes.
     *
     * @return $this
     */
    public function kilobytes()
    {
        return $this->unit('kilobytes');
    }

    /**
     * Set the file size unit to megabytes.
     *
     * @return $this
     */
    public function megabytes()
    {
        return $this->unit('megabytes');
    }

    /**
     * Set the file size unit to gigabytes.
     *
     * @return $this
     */
    public function gigabytes()
    {
        return $this->unit('gigabytes');
    }

    /**
     * Get the file size unit to use.
     *
     * @return 'bytes'|'kilobytes'|'megabytes'|'gigabytes'|string
     */
    public function getUnit()
    {
        if (isset($this->unit)) {
            return $this->unit;
        }

        return static::fallbackUnit();
    }

    /**
     * Get the file size unit to use from the config.
     *
     * @return 'bytes'|'kilobytes'|'megabytes'|'gigabytes'|string
     */
    public static function fallbackUnit()
    {
        return type(config('upload.unit', 'bytes'))->asString();
    }

    /**
     * Get the minimum file size to upload in bytes.
     *
     * @return int
     */
    public function getMinSize(bool $convert = true)
    {
        $minSize = $this->minSize
            ?? static::fallbackMinSize();

        if ($convert) {
            return $this->convertSize($minSize);
        }

        return $minSize;
    }

    /**
     * Get the minimum file size to upload in bytes from the config.
     *
     * @default 0
     *
     * @return int
     */
    public static function fallbackMinSize()
    {
        return type(config('upload.size.min', 0))->asInt();
    }

    /**
     * Get the maximum file size to upload in bytes.
     *
     * @return int
     */
    public function getMaxSize(bool $convert = true)
    {
        $maxSize = $this->maxSize
            ?? static::fallbackMaxSize();

        if ($convert) {
            return $this->convertSize($maxSize);
        }

        return $maxSize;
    }

    /**
     * Get the maximum file size to upload in bytes from the config.
     *
     * @default 1GB
     *
     * @return int
     */
    public static function fallbackMaxSize()
    {
        return type(config('upload.size.max', 1024 ** 3))->asInt();
    }

    /**
     * Convert the provided size make the number of bytes using the unit.
     *
     * @return int
     */
    public function convertSize(int $size)
    {
        return match ($this->getUnit()) {
            'kilobytes' => $size * 1024,
            'megabytes' => $size * (1024 ** 2),
            'gigabytes' => $size * (1024 ** 3),
            default => $size,
        };
    }

    /**
     * Set the accepted file types.
     *
     * @param  string|array<int,string>|\Illuminate\Support\Collection<int,string>  $accepts
     * @return $this
     */
    public function accepts($accepts)
    {
        if ($accepts instanceof Collection) {
            $accepts = $accepts->all();
        }

        $this->accepts = \array_map(
            static fn (string $type) => \str_replace('*', '', $type),
            Arr::wrap($accepts)
        );

        return $this;
    }

    /**
     * Set the accepted file types to all image MIME types.
     *
     * @return $this
     */
    public function acceptsImages()
    {
        return $this->accepts('image/');
    }

    /**
     * Set the accepted file types to all video MIME types.
     *
     * @return $this
     */
    public function acceptsVideos()
    {
        return $this->accepts('video/');
    }

    /**
     * Set the accepted file types to all audio MIME types.
     *
     * @return $this
     */
    public function acceptsAudio()
    {
        return $this->accepts('audio/');
    }

    /**
     * Get the accepted file types.
     *
     * @return array<int,string>
     */
    public function getAccepted()
    {
        if (empty($this->accepts)) {
            return static::fallbackAccepted();
        }

        return $this->accepts;
    }

    /**
     * Get the accepted file types from the config.
     *
     * @return array<int,string>
     */
    public static function fallbackAccepted()
    {
        return type(config('upload.types', []))->asArray();
    }

    /**
     * Set the duration of the presigned URL.
     * If an integer is provided, it will be interpreted as the number of seconds.
     *
     * @param  \Carbon\Carbon|int|string|null  $duration
     * @return $this
     */
    public function duration($duration)
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * Set the duration of the presigned URL.
     * If an integer is provided, it will be interpreted as the number of seconds.
     *
     * @param  \Carbon\Carbon|int|string|null  $expires
     * @return $this
     */
    public function expires($expires)
    {
        $this->duration = $expires;

        return $this;
    }

    /**
     * Set the duration of the presigned URL to a number of seconds.
     *
     * @param  int  $seconds
     * @return $this
     */
    public function seconds($seconds)
    {
        $this->duration = \sprintf('+%d seconds', $seconds);

        return $this;
    }

    /**
     * Set the duration of the presigned URL to a number of minutes.
     *
     * @param  int  $minutes
     * @return $this
     */
    public function minutes($minutes)
    {
        $this->duration = \sprintf('+%d minutes', $minutes);

        return $this;
    }

    /**
     * Get the duration of the presigned URL.
     *
     * @return string
     */
    public function getDuration()
    {
        $duration = $this->duration;

        return match (true) {
            \is_string($duration) => $duration,

            \is_int($duration) => \sprintf('+%d seconds', $duration),

            $duration instanceof Carbon => \sprintf(
                '+%d seconds', \round(\abs($duration->diffInSeconds()))
            ),
            default => static::fallbackDuration(),
        };
    }

    /**
     * Get the duration of the presigned URL from the config.
     *
     * @return string
     */
    public static function fallbackDuration()
    {
        return type(config('upload.expires', '+2 minutes'))->asString();
    }

    /**
     * Set the path to store the file at.
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
     * Get the path to store the file at.
     *
     * @return string|\Closure|null
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set the name, or method, of generating the name of the file to be stored.
     *
     * @param  'uuid'|\Closure|string  $name
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
     * @return 'uuid'|\Closure|string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the ACL to use for the file.
     *
     * @param  string  $accessControlList
     * @return $this
     */
    public function acl($accessControlList)
    {
        $this->acl = $accessControlList;

        return $this;
    }

    /**
     * Get the access control list to use for the file.
     *
     * @return string
     */
    public function getAccessControlList()
    {
        if (isset($this->acl)) {
            return $this->acl;
        }

        return static::fallbackAccessControlList();
    }

    /**
     * Get the access control list to use for the file from the config.
     *
     * @return string
     */
    public static function fallbackAccessControlList()
    {
        return type(config('upload.acl', 'public-read'))->asString();
    }

    /**
     * Set the input to accept multiple files.
     *
     * @return $this
     */
    public function multiple()
    {
        $this->multiple = true;

        return $this;
    }

    /**
     * Determine if the input should accept multiple files.
     *
     * @return bool
     */
    public function isMultiple()
    {
        return $this->multiple;
    }

    /**
     * Get the defaults for form input fields.
     *
     * @param  string  $key
     * @return array<string,mixed>
     */
    public function getFormInputs($key)
    {
        return [
            'acl' => $this->getAccessControlList(),
            'key' => $key,
        ];
    }

    /**
     * Get the policy condition options for the request.
     *
     * @param  string  $key
     * @return array<int,array<int|string,mixed>>
     */
    public function getOptions($key)
    {
        $options = [
            ['eq', '$acl', $this->getAccessControlList()],
            ['eq', '$bucket', $this->getBucket()],
            ['eq', '$key', $key],
            ['content-length-range', $this->getMinSize(), $this->getMaxSize()],
        ];

        $accepts = $this->getAccepted();

        if (filled($accepts)) {
            // Remove any file extensions from the validator, instead using mime types
            $accepts = \implode(',', \array_values(
                \array_filter(
                    $accepts,
                    static fn (string $type) => ! \str_starts_with($type, '.')
                )
            ));

            $options[] = ['starts-with', '$Content-Type', $accepts];
        }

        return $options;
    }

    /**
     * Get a configuration value from the disk.
     *
     * @return mixed
     */
    public function getDiskConfig(string $key)
    {
        return config(
            \sprintf('filesystems.disks.%s.%s', $this->getDisk(), $key)
        );
    }

    /**
     * Get the S3 client to use for uploading files.
     *
     * @return \Aws\S3\S3Client
     */
    public function getClient()
    {
        return new S3Client([
            'version' => 'latest',
            'region' => $this->getDiskConfig('region'),
            'credentials' => [
                'key' => $this->getDiskConfig('key'),
                'secret' => $this->getDiskConfig('secret'),
            ],
        ]);
    }

    /**
     * Get the bucket to use for uploading files.
     *
     * @return string
     */
    public function getBucket()
    {
        return type($this->getDiskConfig('bucket'))->asString();
    }

    /**
     * Validate the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string,mixed>
     */
    public function validate($request)
    {
        return Validator::make(
            $request->all(),
            $this->getValidationRules(),
            $this->getValidationMessages(),
            $this->getValidationAttributes(),
        )->validate();
    }

    /**
     * Get the validation rules for file uploads.
     *
     * @return array<string,array<int,mixed>>
     */
    public function getValidationRules()
    {
        $min = $this->getMinSize();
        $max = $this->getMaxSize();

        return [
            'name' => ['required', 'string', 'max:1024'],
            'type' => ['required', new OfType($this->getAccepted())],
            'size' => ['required', 'integer', 'min:'.$min, 'max:'.$max],
            'meta' => ['nullable'],
        ];
    }

    /**
     * Get the validation messages for file uploads.
     *
     * @return array<string,string>
     */
    public function getValidationMessages()
    {
        $min = $this->getMinSize(false);
        $max = $this->getMaxSize(false);

        return [
            'size.min' => \sprintf(
                'The :attribute must be larger than %d %s.',
                $min,
                $min === 1 ? rtrim($this->getUnit(), 's') : $this->getUnit()
            ),
            'size.max' => \sprintf(
                'The :attribute must be smaller than %d %s.',
                $max,
                $max === 1 ? rtrim($this->getUnit(), 's') : $this->getUnit()
            ),
        ];
    }

    /**
     * Get the attributes for the request.
     *
     * @return array<string,string>
     */
    public function getValidationAttributes()
    {
        return [
            'name' => 'file name',
            'type' => 'file type',
            'size' => 'file size',
        ];
    }

    /**
     * Create a signed upload URL response.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function create()
    {
        $request = $this->getRequest();

        /**
         * @var array{name:string,type:string,size:int,meta:mixed}
         */
        $validated = $this->validate($request);

        $key = $this->buildStorageKey($validated);

        $postObject = new PostObjectV4(
            $this->getClient(),
            $this->getBucket(),
            $this->getFormInputs($key),
            $this->getOptions($key),
            $this->getDuration()
        );

        return response()->json([
            'code' => 200,
            'attributes' => $postObject->getFormAttributes(),
            'inputs' => $postObject->getFormInputs(),
        ]);
    }

    /**
     * Build the storage key path for the uploaded file.
     *
     * @param  array{name:string,type:string,size:int,meta:mixed}  $validated
     * @return string
     */
    public function buildStorageKey($validated)
    {
        /** @var string */
        $filename = Arr::get($validated, 'name');

        $name = $this->getName();

        /** @var string */
        $validatedName = match (true) {
            $name === 'uuid' => Str::uuid()->toString(),
            $name instanceof \Closure => type($this->evaluateValidated($name, $validated))->asString(),
            default => \pathinfo($filename, \PATHINFO_FILENAME),
        };

        $path = $this->evaluateValidated($this->getPath(), $validated);

        return Str::of($validatedName)
            ->append('.', \pathinfo($filename, \PATHINFO_EXTENSION))
            ->when($path, fn (Stringable $name) => $name
                    ->prepend($path, '/') // @phpstan-ignore-line
                    ->replace('//', '/'),
            )->toString();
    }

    /**
     * Evaluate the closure using the validated data
     *
     * @param  \Closure|string|null  $closure
     * @param  array{name:string,type:string,size:int,meta:mixed}  $validated
     * @return string|null
     */
    protected function evaluateValidated($closure, $validated)
    {
        return $this->evaluate($closure, [
            'data' => $validated,
            'validated' => $validated,
            'name' => Arr::get($validated, 'name'),
            'type' => Arr::get($validated, 'type'),
            'size' => Arr::get($validated, 'size'),
            'meta' => Arr::get($validated, 'meta'),
        ]);
    }

    /**
     * Get the upload as form input attributes.
     *
     * @return array<string,mixed>
     */
    public function toArray()
    {
        // Format the types to be a comma separated string
        $accepts = implode(',', \array_map(
            static fn (string $type) => \str_ends_with($type, '/')
                ? $type.'*'
                : $type,
            $this->getAccepted()
        ));

        return [
            'multiple' => $this->isMultiple(),
            'accept' => $accepts,
        ];
    }
}
