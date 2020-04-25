<?php

namespace Wmsamolet\PhpJsonRpc2;

/**
 * This is just an example.
 */
class JsonRpc2Response extends AbstractJsonRpc2
{
    /** @var null|int|string */
    private $id;

    /** @var array|float|int|string */
    private $result;

    /**
     * @param array|string|integer|double $result
     * @param null|string|int $id
     */
    public function __construct($result, $id = null)
    {
        $this->result = $result;
        $this->id = $id;
    }

    public function __toString(): string
    {
        return (string)json_encode(array_merge_recursive([
            'jsonrpc' => '2.0',
            'id' => $this->id,
            'result' => $this->result,
        ], $this->getData()));
    }
}
