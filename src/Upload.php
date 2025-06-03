<?php

declare(strict_types=1);

namespace Honed\Upload;

use Aws\S3\PostObjectV4;
use Honed\Core\Primitive;
use Honed\Upload\Concerns\DispatchesPresignEvents;
use Honed\Upload\Concerns\HasFile;
use Honed\Upload\Concerns\ValidatesUpload;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Number;
use Illuminate\Validation\ValidationException;

use function array_map;
use function array_merge;
use function count;
use function implode;
use function mb_strtoupper;
use function mb_trim;
use function ucfirst;

class Upload extends Primitive implements Responsable
{
    use DispatchesPresignEvents;
    use HasFile;
    use ValidatesUpload;

    /**
     * The upload data to use from the request.
     *
     * @var UploadData|null
     */
    protected $data;

    /**
     * Get the configuration rules for validating file uploads.
     *
     * @var array<int, UploadRule>
     */
    protected $rules = [];

    /**
     * The access control list to use for the file.
     *
     * @var string|null
     */
    protected $acl;

    /**
     * The additional data to return with the presign response.
     *
     * @var mixed
     */
    protected $returns = null;

    /**
     * Whether the upload accepts multiple files.
     *
     * @var bool
     */
    protected $multiple = false;

    /**
     * Whether to only return the upload message.
     *
     * @var bool
     */
    protected $message = false;

    /**
     * Create a new upload instance.
     */
    public function __construct(
        protected Request $request,
    ) {
        parent::__construct();
    }

    /**
     * Create a new upload instance.
     *
     * @param  string|null  $disk
     * @return static
     */
    public static function make($disk = null)
    {
        return resolve(static::class)->disk($disk);
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
     * Get the default access control list to use for the file.
     *
     * @return string
     */
    public static function getDefaultACL()
    {
        return type(config('upload.acl', 'public-read'))->asString();
    }

    /**
     * Set the rules for validating file uploads.
     *
     * @param  UploadRule|iterable<UploadRule>  ...$rules
     * @return $this
     */
    public function rules(...$rules)
    {
        $rules = Arr::flatten($rules);

        $this->rules = array_merge($this->rules, $rules);

        return $this;
    }

    /**
     * Get the rules for validating file uploads.
     *
     * @return array<int, UploadRule>
     */
    public function getRules()
    {
        return $this->rules;
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
     * Set additiional data to return with the presign response.
     *
     * @param  mixed  $return
     * @return $this
     */
    public function provide($return)
    {
        $this->returns = $return;

        return $this;
    }

    /**
     * Define the data that should be provided as part of the presign response.
     *
     * @return mixed
     */
    public function provides()
    {
        return [];
    }

    /**
     * Get the additional data to return with the presign response.
     *
     * @return mixed
     */
    public function getProvided()
    {
        if (isset($this->returns)) {
            return $this->evaluate($this->returns);
        }

        return $this->provides();
    }

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
     * Get the upload data.
     *
     * @return UploadData|null
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set whether to only return the upload message.
     *
     * @param  bool  $message
     * @return $this
     */
    public function message($message = true)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Determine whether to only return the upload message.
     *
     * @return bool
     */
    public function onlyMessage()
    {
        return $this->message;
    }

    /**
     * Create the upload message.
     *
     * @return string
     */
    public function getMessage()
    {
        $extensions = $this->getExtensions();
        $mimes = $this->getMimeTypes();

        $numMimes = count($mimes);
        $numExts = count($extensions);

        $typed = match (true) {
            $numExts > 0 && $numExts < 4 => implode(', ', array_map(
                static fn ($ext) => mb_strtoupper(mb_trim($ext)),
                $extensions
            )),

            $numMimes > 0 && $numMimes < 4 => ucfirst(implode(', ', array_map(
                static fn ($mime) => mb_trim($mime, ' /'),
                $mimes
            ))),

            $this->isMultiple() => 'Files',

            default => 'A single file',
        };

        return $typed.' up to '.Number::fileSize($this->getMax());
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
     * @return array<int,array<string|int,mixed>>
     */
    public function getOptions($key)
    {
        return [
            ['eq', '$acl', $this->getACL()],
            ['eq', '$key', $key],
            ['eq', '$bucket', $this->getBucket()],
            ['content-length-range', $this->getMin(), $this->getMax()],
            ['eq', '$Content-Type', $this->getData()?->type],
        ];
    }

    /**
     * Validate the incoming request.
     *
     * @param  Request|null  $request
     * @return array{UploadData, UploadRule|null}
     *
     * @throws ValidationException
     */
    public function validate($request)
    {
        $request ??= $this->request;

        [$name, $extension] =
            static::destructureFilename($request->input('name'));

        $request->merge([
            'name' => $name,
            'extension' => $extension,
        ])->all();

        $rule = Arr::first(
            $this->getRules(),
            static fn (UploadRule $rule) => $rule->isMatching(
                $request->input('type'),
                $extension,
            ),
        );

        try {
            $validated = Validator::make(
                $request->all(),
                $rule?->createRules() ?? $this->createRules(),
                [],
                $this->getAttributes(),
            )->validate();

            return [UploadData::from($validated), $rule];
        } catch (ValidationException $e) {
            $this->failedPresign($request);

            throw $e;
        }
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
     * @param  Request|null  $request
     * @return array{attributes:array<string,mixed>,inputs:array<string,mixed>}
     *
     * @throws ValidationException
     */
    public function create($request = null)
    {
        [$data, $rule] = $this->validate($request);

        $this->data = $data;

        $key = $this->createKey($data);

        $postObject = new PostObjectV4(
            $this->getClient(),
            $this->getBucket(),
            $this->getFormInputs($key),
            $this->getOptions($key),
            $this->formatExpiry($rule ? $rule->getExpiry() : $this->getExpiry())
        );

        static::createdPresign($data, $this->getDisk());

        return [
            'attributes' => $postObject->getFormAttributes(),
            'inputs' => $postObject->getFormInputs(),
            'data' => $this->getProvided(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function toArray($named = [], $typed = [])
    {
        $data = [
            'multiple' => $this->isMultiple(),
            'message' => $this->getMessage(),
        ];

        if ($this->onlyMessage()) {
            return $data;
        }

        return array_merge($data, [
            'extensions' => $this->getExtensions(),
            'mimes' => $this->getMimeTypes(),
            'size' => $this->getMax(),
        ]);
    }

    /**
     * Create a response for the upload.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function toResponse($request)
    {
        $presign = $this->create($request);

        return response()->json($presign);
    }

    /**
     * {@inheritdoc}
     */
    protected function resolveDefaultClosureDependencyForEvaluationByName($parameterName)
    {
        $data = $this->getData();

        if ($parameterName === 'bucket') {
            return [$this->getBucket()];
        }

        if (! $data) {
            return parent::resolveDefaultClosureDependencyForEvaluationByName($parameterName);
        }

        return match ($parameterName) {
            'data' => [$data],
            'key' => [$this->createKey($data)],
            'file' => [$this->createFilename($data).'.'.$data->extension],
            'filename' => [$this->createFilename($data)],
            'folder' => [$this->getFolder($this->createKey($data))],
            'name' => [$data->name],
            'extension' => [$data->extension],
            'type' => [$data->type],
            'size' => [$data->size],
            'meta' => [$data->meta],
            'disk' => [$this->getDisk()],
            default => parent::resolveDefaultClosureDependencyForEvaluationByName($parameterName),
        };
    }

    /**
     * {@inheritdoc}
     */
    protected function resolveDefaultClosureDependencyForEvaluationByType($parameterType)
    {
        if ($parameterType === UploadData::class && isset($this->data)) {
            return [$this->data];
        }

        return parent::resolveDefaultClosureDependencyForEvaluationByType($parameterType);
    }
}
