<?php

declare(strict_types=1);

namespace Honed\Upload\Concerns;

trait Returnable
{
    /**
     * The data to return with the presign response.
     *
     * @var mixed
     */
    protected $return;

    /**
     * Set the data to return with the presign response.
     * 
     * @param  mixed  $data
     * @return $this
     */
    public function returning($data)
    {
        $this->return = $data;

        return $this;
    }

    /**
     * Get the data to return with the presign response.
     *
     * @return mixed
     */
    public function getReturn()
    {
        return $this->evaluate($this->return);
    }
}