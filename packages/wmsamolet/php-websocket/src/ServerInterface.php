<?php

namespace Wmsamolet\PhpWebsocket;

interface ServerInterface
{
    public const ON_OPEN = 'open';
    public const ON_CLOSE = 'close';
    public const ON_MESSAGE = 'message';
    public const ON_ERROR = 'error';

    public function start(): bool;

    public function stop(): bool;

    public function on(string $eventName, callable $eventCallback): void;

    public function getClients(): ServerClientCollection;
}