<?php

declare(strict_types=1);

namespace Honed\Upload;

use Honed\Core\Primitive;
use Illuminate\Http\Request;
use Honed\Upload\Pipes\Presign;
use Honed\Upload\Pipes\Validate;
use Honed\Core\Concerns\HasRequest;
use Honed\Upload\Pipes\CreateRules;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Validation\ValidationException;
use Honed\Upload\Exceptions\PresignNotGeneratedException;

class Upload extends Primitive implements Responsable
{
    use Concerns\BridgesSerialization;
    use Concerns\HasFile;
    use Concerns\HasRules;
    use Concerns\InteractsWithS3;
    use Concerns\ValidatesUpload;
    use HasRequest;
    // use HasPipeline;

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
     * Use the upload configuration to build the message.
     *
     * @return string
     */
    public function message()
    {
        return $this->getMessage(
            $this->getFile()->getSize(),
            $this->getExtensions(),
            $this->getMimeTypes()
        );
    }
    /**
     * Create a presigned POST URL using.
     *
     * @return array{attributes:array<string,mixed>,inputs:array<string,mixed>}
     *
     * @throws ValidationException
     * @throws PresignNotGeneratedException
     */
    public function create()
    {
        $this->build();

        $presign = $this->getPresign();

        if (! $presign) {
            PresignNotGeneratedException::throw();
        }

        return [
            'attributes' => $presign->getFormAttributes(),
            'inputs' => $presign->getFormInputs(),
            'data' => $this->getResponse(),
        ];
    }

    /**
     * Get the instance as an array.
     * 
     * @return array<string,mixed>
     */
    public function toArray()
    {
        return [
            'multiple' => $this->isMultiple(),
            'message' => $this->message(),
            'extensions' => $this->getExtensions(),
            'mimes' => $this->getMimeTypes(),
            'size' => $this->getMaxSize(),
        ];
    }

    /**
     * Create a response for the upload.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function toResponse($request)
    {
        $this->request($request);

        return response()->json($this->create());
    }

    /**
     * Define the settings for the upload.
     *
     * @param  $this  $upload
     * @return $this
     */
    protected function definition(self $upload): self
    {
        return $upload;
    }

    protected function pipes()
    {
        return [
            CreateRules::class,
            Validate::class,
            Presign::class,
        ];
    }

    /**
     * Provide a selection of default dependencies for evaluation by name.
     *
     * @param  string  $parameterName
     * @return array<int, mixed>
     */
    protected function resolveDefaultClosureDependencyForEvaluationByName($parameterName)
    {
        return match ($parameterName) {
            'file' => [$this->getFile()],
            'bucket' => [$this->getBucket()],
            'disk' => [$this->getDisk()],
            'rule' => [$this->getRule()],
            default => parent::resolveDefaultClosureDependencyForEvaluationByName($parameterName),
        };
    }

    /**
     * Provide a selection of default dependencies for evaluation by type.
     *
     * @param  class-string  $parameterType
     * @return array<int, mixed>
     */
    protected function resolveDefaultClosureDependencyForEvaluationByType($parameterType)
    {
        return match ($parameterType) {
            File::class => [$this->getFile()],
            default => parent::resolveDefaultClosureDependencyForEvaluationByType($parameterType),
        };
    }
}
