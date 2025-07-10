<?php

declare(strict_types=1);

namespace Honed\Upload;

use Honed\Core\Concerns\HasLifecycleHooks;
use Honed\Core\Concerns\HasPipeline;
use Honed\Core\Concerns\HasRequest;
use Honed\Core\Contracts\HooksIntoLifecycle;
use Honed\Core\Pipes\CallsAfter;
use Honed\Core\Pipes\CallsBefore;
use Honed\Core\Primitive;
use Honed\Upload\Concerns\BridgesSerialization;
use Honed\Upload\Concerns\HasFile;
use Honed\Upload\Concerns\HasRules;
use Honed\Upload\Concerns\HasValidatedInput;
use Honed\Upload\Concerns\InteractsWithS3;
use Honed\Upload\Concerns\Returnable;
use Honed\Upload\Concerns\ValidatesUpload;
use Honed\Upload\Exceptions\PresignNotGeneratedException;
use Honed\Upload\Pipes\CreateRules;
use Honed\Upload\Pipes\Presign;
use Honed\Upload\Pipes\SetFile;
use Honed\Upload\Pipes\Validate;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\ValidationException;

/**
 * @extends \Honed\Core\Primitive<string, mixed>
 */
class Upload extends Primitive implements HooksIntoLifecycle, Responsable
{
    use BridgesSerialization;
    use HasFile;
    use HasLifecycleHooks;
    use HasPipeline;
    use HasRequest;
    use HasRules;
    use HasValidatedInput;
    use InteractsWithS3;
    use Returnable;
    use ValidatesUpload;

    /**
     * The identifier to use for evaluation.
     *
     * @var string
     */
    protected $evaluationIdentifier = 'upload';

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
     */
    public static function make(): static
    {
        return resolve(static::class);
    }

    /**
     * Create a new upload instance for the given disk.
     */
    public static function into(string $disk): static
    {
        return static::make()->disk($disk);
    }

    /**
     * Get the attributes for the validator.
     *
     * @return array<string,string>
     */
    public function getAttributes(): array
    {
        return [
            'name' => 'file name',
            'extension' => 'file extension',
            'type' => 'file type',
            'size' => 'file size',
        ];
    }

    /**
     * Get the message for the upload file input.
     *
     * @return string
     */
    public function message()
    {
        return $this->getMessage(
            $this->getMaxSize(),
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
        $this->define();

        $this->build();

        $presign = $this->getPresign();

        if (! $presign) {
            PresignNotGeneratedException::throw();
        }

        return [
            'attributes' => $presign->getFormAttributes(),
            'inputs' => $presign->getFormInputs(),
            'data' => $this->getReturn(),
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
     * Resolve the upload instance from the container.
     */
    protected function resolve(): void
    {
        self::__construct(App::make(Request::class));
    }

    /**
     * Get the representation of the instance.
     *
     * @return array<string, mixed>
     */
    protected function representation(): array
    {
        $this->define();

        return [
            'multiple' => $this->isMultiple(),
            'message' => $this->message(),
            'extensions' => $this->getExtensions(),
            'mimes' => $this->getMimeTypes(),
            'size' => $this->getMaxSize(),
        ];
    }

    /**
     * Get the pipes to be used.
     *
     * @return array<int,class-string<\Honed\Core\Pipe<self>>>
     */
    protected function pipes(): array
    {
        // @phpstan-ignore-next-line
        return [
            CallsBefore::class,
            CreateRules::class,
            Validate::class,
            SetFile::class,
            Presign::class,
            CallsAfter::class,
        ];
    }

    /**
     * Provide a selection of default dependencies for evaluation by name.
     *
     * @return array<int, mixed>
     */
    protected function resolveDefaultClosureDependencyForEvaluationByName(string $parameterName): array
    {
        return match ($parameterName) {
            'request' => [$this->getRequest()],
            'file' => [$this->getFile()],
            'bucket' => [$this->getBucket()],
            'disk' => [$this->getDisk()],
            'validated' => [$this->getValidated()],
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
    protected function resolveDefaultClosureDependencyForEvaluationByType(string $parameterType): array
    {
        return match ($parameterType) {
            UploadRule::class => [$this->getRule()],
            File::class => [$this->getFile()],
            Request::class => [$this->getRequest()],
            default => parent::resolveDefaultClosureDependencyForEvaluationByType($parameterType),
        };
    }
}
