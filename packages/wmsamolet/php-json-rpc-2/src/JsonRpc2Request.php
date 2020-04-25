<?php

namespace Wmsamolet\PhpJsonRpc2;

/**
 * This is just an example.
 */
class JsonRpc2Request extends AbstractJsonRpc2
{
    /** @var null|string|int */
    private $id;

    /** @var string */
    private $method;

    /** @var array */
    private $params;

    /**
     * @param null|string|int $id
     */
    public function __construct(string $method, array $params = [], $id = null)
    {
        $this->id = $id;
        $this->method = $method;
        $this->params = $params;
    }

    /**
     * @return null|int|string
     */
    public function getId()
    {
        return $this->id;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function __toString(): string
    {
        return (string)json_encode(array_merge_recursive([
            'jsonrpc' => '2.0',
            'id' => $this->id,
            'method' => $this->method,
            'params' => $this->params
        ], $this->getData()));
    }
}
