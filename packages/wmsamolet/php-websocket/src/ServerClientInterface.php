<?php

namespace Wmsamolet\PhpWebsocket;

use ArrayObject;
use Throwable;

interface ServerClientInterface
{
    public function getId(): string;

    public function getServer(): ServerInterface;

    public function getStorage(): ArrayObject;

    public function getMessage(): ?string;

    public function setMessage(string $message = null): void;

    public function getException(): ?Throwable;

    public function setException(Throwable $exception = null): void;

    public function sendMessage(string $message): bool;
}