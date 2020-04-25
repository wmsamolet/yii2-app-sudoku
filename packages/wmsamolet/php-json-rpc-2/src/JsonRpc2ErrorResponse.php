<?php

namespace Wmsamolet\PhpJsonRpc2;

class JsonRpc2ErrorResponse extends AbstractJsonRpc2
{
    /** @var null|int|string */
    private $id;

    /** @var null|int|string */
    private $errorCode;

    /** @var null|string */
    private $errorMessage;

    /**
     * @param int|string|null $errorCode
     * @param string|null $errorMessage
     * @param null|string|int $id
     */
    public function __construct($errorCode, string $errorMessage = null, $id = null)
    {
        $this->id = $id;
        $this->errorCode = $errorCode;
        $this->errorMessage = $errorMessage;
    }

    /**
     * @return null|int|string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int|string
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function __toString(): string
    {
        return (string)json_encode(array_merge_recursive([
            'jsonrpc' => '2.0',
            'id' => $this->id,
            'error' => [
                'code' => $this->errorCode,
                'message' => $this->errorMessage
            ],
        ], $this->getData()));
    }
}
