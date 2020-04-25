<?php

namespace Wmsamolet\PhpJsonRpc2;

abstract class AbstractJsonRpc2
{
    /** @var array */
    private $data = [];

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }
}