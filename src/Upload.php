<?php

declare(strict_types=1);

namespace Honed\Upload;

use Aws\S3\PostObjectV4;
use Aws\S3\S3Client;
use Honed\Core\Concerns\HasRequest;
use Honed\Core\Primitive;
use Honed\Upload\Concerns\ValidatesUpload;
use Honed\Upload\Contracts\AnonymizesName;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

class Upload extends Primitive implements Responsable
{
    use HasRequest;
    use ValidatesUpload;

    /**
     * The disk to retrieve the S3 credentials from.
     *
     * @var string|null
     */
    protected $disk;

    /**
     * Get the configuration rules for validating file uploads.
     *
     * @var array<int, \Honed\Upload\UploadRule>
     */
    protected $rules = [];

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
     * Whether the file name should be anonymized using a UUID.
     *
     * @var bool|null
     */
    protected $anonymize;

    /**
     * The access control list to use for the file.
     *
     * @var string|null
     */
    protected $acl;

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
     * @param  string  $disk
     * @return static
     */
    public static function make($disk = null)
    {
        return resolve(static::class)
            ->disk($disk);
    }

    /**
     * Create a new upload instance for the given disk.
     *
     * @param  string  $disk
     * @return static
     */
    public static function into($disk)
    {
        return static::make($disk);
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
     * Set the rules for validating file uploads.
     *
     * @param  iterable<\Honed\Upload\UploadRule>  ...$rules
     * @return $this
     */
    public function rules(...$rules)
    {
        $rules = Arr::flatten($rules);

        $this->rules = \array_merge($this->rules, $rules);

        return $this;
    }

    /**
     * Get the rules for validating file uploads.
     *
     * @return array<int, \Honed\Upload\UploadRule>
     */
    public function getRules()
    {
        return $this->rules;
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

        if ($this instanceof AnonymizesName) {
            return true;
        }

        return static::isAnonymizedByDefault();
    }

    /**
     * Determine if the file name should be anonymized using a UUID by default.
     *
     * @return bool
     */
    public static function isAnonymizedByDefault()
    {
        return (bool) config('upload.anonymize', false);
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
        return $this->acl ?? static::getDefaultACL();
    }

    /**
     * Get the default access control list to use for the file.
     *
     * @return string
     */
    public static function getDefaultACL()
    {
        return type(config('upload.acl', 'public-read'))->asString();
    }

    /**
     * Get the S3 client to use for uploading files.
     *
     * @return \Aws\S3\S3Client
     */
    public function getClient()
    {
        $disk = $this->getDisk();

        return new S3Client([
            'version' => 'latest',
            'region' => config("filesystems.disks.{$disk}.region"),
            'credentials' => [
                'key' => config("filesystems.disks.{$disk}.key"),
                'secret' => config("filesystems.disks.{$disk}.secret"),
            ],
        ]);
    }

    /**
     * Get the S3 bucket to use for uploading files.
     *
     * @return string
     */
    public function getBucket()
    {
        $disk = $this->getDisk();

        return type(config("filesystems.disks.{$disk}.bucket"))->asString();
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
            ['eq', '$key', $key],
            ['eq', '$bucket', $this->getBucket()],
            ['content-length-range', $this->getMin(), $this->getMax()],
        ];

        $mimes = $this->getMimes();

        if (filled($mimes)) {
            $options[] = ['starts-with', '$Content-Type', \implode(',', $mimes)];
        }

        return $options;
    }

    /**
     * Build the storage key path for the uploaded file.
     *
     * @param  \Honed\Upload\UploadData  $data
     * @return string
     */
    public function createKey($data)
    {
        $name = $this->getName();

        $filename = match (true) {
            $this->isAnonymized() => Str::uuid()->toString(),
            $name instanceof \Closure => type($this->evaluateValidated($name, $data))->asString(),
            default => $data->name,
        };

        $path = $this->evaluateValidated($this->getPath(), $data);

        return Str::of($filename)
            ->append('.', $data->extension)
            ->when($path, fn (Stringable $name, $path) => $name
                ->prepend($path, '/')
                ->replace('//', '/'),
            )->trim('/')
            ->value();
    }

    /**
     * Evaluate the closure using the validated data
     *
     * @param  \Closure|string|null  $closure
     * @param  \Honed\Upload\UploadData  $data
     * @return string|null
     */
    protected function evaluateValidated($closure, $data)
    {
        return $this->evaluate($closure, [
            'data' => $data,
            'name' => $data->name,
            'extension' => $data->extension,
            'type' => $data->type,
            'size' => $data->size,
            'meta' => $data->meta,
        ], [
            UploadData::class => $data,
        ]);
    }

    /**
     * Validate the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Honed\Upload\UploadRule|null  $rule
     * @return \Honed\Upload\UploadData
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function validate($request, $rule = null)
    {
        $rules = $rule ? $rule->createRules() : $this->createRules();

        $validated = Validator::make(
            $request->all(),
            $rules,
            [],
            $this->getAttributes(),
        )->validate();

        return UploadData::from($validated);
    }

    /**
     * Get the attributes for the validator.
     *
     * @return array<string,string>
     */
    public function getAttributes()
    {
        return [
            'name' => 'file name',
            'extension' => 'file extension',
            'type' => 'file type',
            'size' => 'file size',
        ];
    }

    /**
     * Create a presigned POST URL using.
     *
     * @param  \Illuminate\Http\Request|null  $request
     * @return array{attributes:array<string,mixed>,inputs:array<string,mixed>}
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function create($request = null)
    {
        $request ??= $this->getRequest();

        [$name, $extension] =
            static::destructureFilename($request->input('name'));

        $request->merge([
            'name' => $name,
            'extension' => $extension,
        ])->all();

        $rule = Arr::first(
            $this->getRules(),
            static fn (UploadRule $rule) => $rule->isMatching($request->input('type'), $extension),
        );

        $validated = $this->validate($request, $rule);

        $key = $this->createKey($validated);

        $postObject = new PostObjectV4(
            $this->getClient(),
            $this->getBucket(),
            $this->getFormInputs($key),
            $this->getOptions($key),
            $this->getExpiry()
        );

        return [
            'attributes' => $postObject->getFormAttributes(),
            'inputs' => $postObject->getFormInputs(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [];
    }

    /**
     * Create a response for the upload.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function toResponse($request)
    {
        $presign = $this->create($request);

        return response()->json($presign);
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
