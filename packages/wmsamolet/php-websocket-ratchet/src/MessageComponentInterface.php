<?php

namespace Wmsamolet\PhpWebsocket\Ratchet;

use Wmsamolet\PhpWebsocket\ServerInterface;

interface MessageComponentInterface extends \Ratchet\MessageComponentInterface
{
    public function getServer(): ServerInterface;

    public function setServer(ServerInterface $server): void;

    public function addEventCallback(string $eventName, callable $eventCallback): void;
}