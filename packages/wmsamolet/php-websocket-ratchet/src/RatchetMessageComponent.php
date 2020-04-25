<?php

namespace Wmsamolet\PhpWebsocket\Ratchet;

use Exception;
use Ratchet\ConnectionInterface;
use Wmsamolet\PhpWebsocket\ServerInterface;

class RatchetMessageComponent implements MessageComponentInterface
{
    /** @var \Wmsamolet\PhpWebsocket\Ratchet\RatchetServer */
    protected $server;
    protected $callbacks = [];

    public function getServer(): ServerInterface
    {
        return $this->server;
    }

    public function setServer(ServerInterface $server): void
    {
        $this->server = $server;
    }

    public function onOpen(ConnectionInterface $connection): void
    {
        $this->getServer()->getClients()->add(
            new RatchetServerClient($this->server, $connection)
        );

        $this->processEventCallbacks(ServerInterface::ON_OPEN, $connection);
    }

    public function onClose(ConnectionInterface $connection): void
    {
        $this->processEventCallbacks(ServerInterface::ON_CLOSE, $connection);

        $this->getServer()->getClients()->remove(
            RatchetServerClient::getIdByConnection($connection)
        );
    }

    public function onError(ConnectionInterface $connection, Exception $exception): void
    {
        $client = $this->getServer()->getClients()->get(
            RatchetServerClient::getIdByConnection($connection)
        );

        $client->setMessage();
        $client->setException($exception);

        $this->processEventCallbacks(ServerInterface::ON_ERROR, $connection);
    }

    public function onMessage(ConnectionInterface $connection, $message): void
    {
        $client = $this->getServer()->getClients()->get(
            RatchetServerClient::getIdByConnection($connection)
        );

        $client->setMessage($message);
        $client->setException();

        $this->processEventCallbacks(ServerInterface::ON_MESSAGE, $connection);
    }

    public function addEventCallback(string $eventName, callable $eventCallback): void
    {
        if (!isset($this->callbacks[$eventName])) {
            $this->callbacks[$eventName] = [];
        }

        $this->callbacks[$eventName][] = $eventCallback;
    }

    protected function processEventCallbacks(
        string $eventName,
        ConnectionInterface $connection
    ): void {
        if (!isset($this->callbacks[$eventName]) || !is_array($this->callbacks[$eventName])) {
            return;
        }

        foreach ($this->callbacks[$eventName] as $callback) {
            $callback(
                $this->getServer()->getClients()->get(
                    RatchetServerClient::getIdByConnection($connection)
                )
            );
        }
    }
}