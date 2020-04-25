<?php

namespace Wmsamolet\PhpWebsocket\Ratchet;

use ArrayObject;
use Ratchet\ConnectionInterface;
use Throwable;
use Wmsamolet\PhpWebsocket\ServerClientInterface;
use Wmsamolet\PhpWebsocket\ServerInterface;

class RatchetServerClient implements ServerClientInterface
{
    /** @var string */
    private $id;

    /** @var \Wmsamolet\PhpWebsocket\Ratchet\RatchetServer */
    private $server;

    /** @var ArrayObject */
    private $storage;

    /** @var \Ratchet\ConnectionInterface */
    private $connection;

    /** @var null|string */
    private $message;

    /** @var null|\Throwable */
    private $exception;

    public function __construct(
        ServerInterface $server,
        ConnectionInterface $connection
    ) {
        $this->server = $server;
        $this->connection = $connection;

        $this->id = static::getIdByConnection($connection);
        $this->storage = new ArrayObject();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getServer(): ServerInterface
    {
        return $this->server;
    }

    public function getStorage(): ArrayObject
    {
        return $this->storage;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message = null): void
    {
        $this->message = $message;
    }

    public function getException(): ?Throwable
    {
        return $this->exception;
    }

    public function setException(Throwable $exception = null): void
    {
        $this->exception = $exception;
    }

    public function sendMessage(string $message): bool
    {
        $this->connection->send($message);

        return true;
    }

    public static function getIdByConnection(ConnectionInterface $connection)
    {
        return spl_object_hash($connection);
    }
}