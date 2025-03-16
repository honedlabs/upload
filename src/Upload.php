<?php

declare(strict_types=1);

namespace Honed\Upload;

use Aws\S3\PostObjectV4;
use Aws\S3\S3Client;
use Carbon\Carbon;
use Honed\Core\Concerns\HasRequest;
use Honed\Core\Primitive;
use Honed\Upload\Concerns\HasExpires;
use Honed\Upload\Concerns\HasFileRules;
use Honed\Upload\Concerns\HasFileTypes;
use Honed\Upload\Concerns\HasMax;
use Honed\Upload\Concerns\HasMin;
use Honed\Upload\Rules\OfType;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;

/**
 * @extends \Honed\Core\Primitive<string,mixed>
 */
class Upload extends Primitive //implements Responsable
{
    use HasExpires;
    use HasFileRules;
    use HasFileTypes;
    use HasMax;
    use HasMin;
    use HasRequest;

    /**
     * The disk to retrieve the S3 credentials from.
     *
     * @var string|null
     */
    protected $disk;

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
        parent::__construct();
        $this->request($request);
    }

    /**
     * Create a new upload instance.
     *
     * @return static
     */
    public static function make()
    {
        return resolve(static::class);
    }

    /**
     * Create a new upload instance for the given disk.
     *
     * @param  string  $disk
     * @return static
     */
    public static function into($disk)
    {
        return static::make()->disk($disk);
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
        return $this->disk ?? static::fallbackDisk();
    }

    /**
     * Get the disk to use for uploading files from the config.
     *
     * @return string
     */
    public static function fallbackDisk()
    {
        return type(config('upload.disk', 's3'))->asString();
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
     * @return 'uuid'|string|\Closure|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the access control list to use for the file.
     *
     * @param  string  $acl
     * @return $this
     */
    public function acl($acl)
    {
        $this->acl = $acl;

        return $this;
    }

    /**
     * Get the access control list to use for the file.
     *
     * @return string
     */
    public function getACL()
    {
        return $this->acl ?? static::fallbackACL();
    }

    /**
     * Get the access control list to use for the file from the config.
     *
     * @return string
     */
    public static function fallbackACL()
    {
        return type(config('upload.acl', 'public-read'))->asString();
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
            'acl' => $this->getACL(),
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
            ['eq', '$acl', $this->getACL()],
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
