<?php

declare(strict_types=1);

namespace Honed\Upload\Concerns;

use Honed\Upload\Exceptions\CouldNotResolveBucketException;
use Illuminate\Support\Facades\Storage;

trait InteractsWithS3
{
    /**
     * The policy to use for the uploads ACL.
     *
     * @var string
     */
    protected $policy = 'private';

    /**
     * The filesystem disk to use.
     *
     * @var string
     */
    protected $disk = 's3';

    /**
     * The S3 bucket to use.
     *
     * @var string|null
     */
    protected $bucket;

    /**
     * The presigned object.
     *
     * @var \Aws\S3\PostObjectV4|null
     */
    protected $presign;

    /**
     * Set the policy to use for the uploads ACL.
     *
     * @param  string  $policy
     * @return $this
     */
    public function policy($policy)
    {
        $this->policy = $policy;

        return $this;
    }

    /**
     * Set the policy to use for the uploads ACL to public-read.
     *
     * @return $this
     */
    public function publicRead()
    {
        return $this->policy('public-read');
    }

    /**
     * Set the policy to use for the uploads ACL to private.
     *
     * @return $this
     */
    public function private()
    {
        return $this->policy('private');
    }

    /**
     * Get the access control list policy.
     *
     * @return string
     */
    public function getPolicy()
    {
        return $this->policy;
    }

    /**
     * Set the filesystem disk to use.
     *
     * @param  string  $disk
     * @return $this
     */
    public function disk($disk = 's3')
    {
        $this->disk = $disk;

        return $this;
    }

    /**
     * Get the filesystem disk to use.
     *
     * @return string
     */
    public function getDisk()
    {
        return $this->disk;
    }

    /**
     * Set the S3 bucket to use.
     *
     * @param  string  $bucket
     * @return $this
     */
    public function bucket($bucket)
    {
        $this->bucket = $bucket;

        return $this;
    }

    /**
     * Get the S3 bucket to use.
     *
     * @return string
     *
     * @throws CouldNotResolveBucketException
     */
    public function getBucket()
    {
        // @phpstan-ignore assign.propertyType
        $this->bucket ??= config("filesystems.disks.{$this->getDisk()}.bucket");

        if (! $this->bucket) {
            CouldNotResolveBucketException::throw();
        }

        // @phpstan-ignore return.type
        return $this->bucket;
    }

    /**
     * Get the form inputs for the S3 presigner.
     *
     * @param  string  $key
     * @return array{acl:string,key:string}
     */
    public function getFormInputs($key)
    {
        return [
            'acl' => $this->getPolicy(),
            'key' => $key,
        ];
    }

    /**
     * Get the policy options for the request.
     *
     * @param  string  $key
     * @param  string  $mimeType
     * @param  int  $size
     * @return array<int,array<int,string|int>>
     *
     * @throws CouldNotResolveBucketException
     */
    public function getOptions($key, $mimeType, $size)
    {
        return [
            ['eq', '$acl', $this->getPolicy()],
            ['eq', '$key', $key],
            ['eq', '$bucket', $this->getBucket()],
            ['content-length-range', $size, $size], // Must be equal to the size of the uploaded file
            ['eq', '$Content-Type', $mimeType],
        ];
    }

    /**
     * Set the presigned object.
     *
     * @param  \Aws\S3\PostObjectV4|null  $presign
     * @return void
     */
    public function setPresign($presign)
    {
        $this->presign = $presign;
    }

    /**
     * Get the presigned object.
     *
     * @return \Aws\S3\PostObjectV4|null
     */
    public function getPresign()
    {
        return $this->presign;
    }

    /**
     * Get an instance of the S3 client.
     *
     * @return \Aws\S3\S3Client
     */
    public function getClient()
    {
        /** @var \Illuminate\Filesystem\AwsS3V3Adapter */
        $client = Storage::disk($this->getDisk());

        return $client->getClient();
    }
}
