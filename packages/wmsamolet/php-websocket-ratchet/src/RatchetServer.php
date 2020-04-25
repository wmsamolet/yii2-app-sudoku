<?php

namespace Wmsamolet\PhpWebsocket\Ratchet;

use InvalidArgumentException;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Wmsamolet\PhpWebsocket\ServerClientCollection;
use Wmsamolet\PhpWebsocket\ServerInterface;

class RatchetServer implements ServerInterface
{
    protected $messageComponent;
    protected $serverComponent;
    protected $clients;

    public function __construct(MessageComponentInterface $messageComponent, array $config = [])
    {
        $serverPort = $config['port'] ?? null;

        if (!is_int($serverPort)) {
            throw new InvalidArgumentException('The "port" parameter must be specified');
        }

        $this->clients = new ServerClientCollection();

        $this->messageComponent = $messageComponent;
        $this->messageComponent->setServer($this);

        $this->serverComponent = IoServer::factory(
            new HttpServer(
                new WsServer(
                    $this->messageComponent
                )
            ),
            $serverPort
        );
    }

    public function __destruct()
    {
        $this->stop();
    }

    public function start(): bool
    {
        $this->serverComponent->run();

        return true;
    }

    public function stop(): bool
    {
        $this->serverComponent->socket->close();

        return true;
    }

    public function on(string $eventName, callable $eventCallback): void
    {
        $this->messageComponent->addEventCallback($eventName, $eventCallback);
    }

    public function getClients(): ServerClientCollection
    {
        return $this->clients;
    }
}