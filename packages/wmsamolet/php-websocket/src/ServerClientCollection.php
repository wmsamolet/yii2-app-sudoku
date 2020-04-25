<?php

namespace Wmsamolet\PhpWebsocket;

use InvalidArgumentException;
use Wmsamolet\PhpCollections\AbstractCollection;

class ServerClientCollection extends AbstractCollection
{
    /**
     * @param int $key
     * @param ServerClientInterface $value
     *
     * @return bool
     */
    public function check($key, $value): bool
    {
        if (!($value instanceof ServerClientInterface)) {
            throw new InvalidArgumentException(
                'Value must be equal ' . ServerClientInterface::class . ': ' . gettype($value)
            );
        }

        if ($key !== $this->generateValueKey($value)) {
            throw new InvalidArgumentException(
                'Key must be equal ' . ServerClientInterface::class . '::getId(): ' . $key
            );
        }

        return true;
    }

    public function get($key): ServerClientInterface
    {
        return parent::get($key);
    }

    /**
     * @param ServerClientInterface $serverClient
     *
     * @return string
     */
    protected function generateValueKey($serverClient): string
    {
        return $serverClient->getId();
    }
}