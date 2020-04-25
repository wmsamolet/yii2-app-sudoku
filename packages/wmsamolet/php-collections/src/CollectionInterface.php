<?php

namespace Wmsamolet\PhpCollections;

use ArrayAccess;
use Countable;
use Iterator;

interface CollectionInterface extends Iterator, ArrayAccess, Countable, Arrayable
{
    /**
     * Sets the iterator data collection
     *
     * @param \Iterator $iterator
     *
     * @return mixed
     */
    public function setIterator(Iterator $iterator);

    /**
     * Setting the callback function to be called when the count of the number of objects in the collection
     *
     * @param callable $callback
     *
     * @return mixed
     */
    public function setCountCallback(callable $callback);

    /**
     * Gets the number of objects in the collection
     *
     * @return int
     */
    public function count(): int;

    /**
     * Verifies conformance of the key and object in the collection
     *
     * @param $key
     * @param $value
     *
     * @return bool
     */
    public function check($key, $value): bool;

    /**
     * Checks the existence of a key in the collection
     *
     * @param $key
     *
     * @return bool
     */
    public function has($key): bool;

    /**
     * Gets the collection object by key
     *
     * @param $key
     *
     * @return mixed
     */
    public function get($key);

    /**
     * Gets all the objects in the collection
     *
     * WARNING: When a large amount of data (objects) the situation may arise out of memory
     * Please be careful!
     *
     * @throws \Wmsamolet\PhpCollections\CollectionException
     *
     * @return object[]
     */
    public function getAll(): array;

    /**
     * Sets the object to the collection for the specified key
     *
     * @param $key
     * @param $value
     *
     * @throws \Wmsamolet\PhpCollections\CollectionException
     *
     * @return void
     */
    public function set($key, $value): void;

    /**
     * Sets the list of objects in the collection
     *
     * @param array $items
     */
    public function setAll(array $items): void;

    /**
     * Removes the object from the collection based on its key
     *
     * @param $key
     *
     * @return mixed
     */
    public function remove($key);
}