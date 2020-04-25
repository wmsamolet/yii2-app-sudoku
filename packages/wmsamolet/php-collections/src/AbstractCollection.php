<?php
/** @noinspection PhpUnusedParameterInspection */

namespace Wmsamolet\PhpCollections;

use ArrayIterator;
use Iterator;

abstract class AbstractCollection implements CollectionInterface
{
    /** @var ArrayIterator */
    private $iterator;

    /** @var callable|null */
    protected $countCallback;

    /**
     * Generates the key for each object in the collection by their obtaining and installing
     *
     * @param object $value
     *
     * @return string|int
     */
    abstract protected function generateValueKey($value);

    public function __construct(array $items = [])
    {
        $this->setIterator(new ArrayIterator());

        if (count($items)) {
            $this->setAll($items);
        }
    }

    /**
     * Sets the iterator data collection
     *
     * @param \Iterator $iterator
     *
     * @return self
     */
    public function setIterator(Iterator $iterator): self
    {
        $this->iterator = new ArrayIteratorIterator($iterator);

        return $this;
    }

    /**
     * Gets an iterator of data collection
     *
     * @return \Iterator
     */
    public function getIterator(): Iterator
    {
        return $this->iterator;
    }

    /**
     * Setting the callback function to be called when the count of the number of objects in the collection
     *
     * @param callable $callback
     *
     * @return static
     */
    public function setCountCallback(callable $callback): self
    {
        $this->countCallback = $callback;

        return $this;
    }

    /**
     * Gets the number of objects in the collection
     *
     * @param bool $useCallback
     *
     * @return int
     */
    public function count(bool $useCallback = true): int
    {
        return $useCallback && is_callable($this->countCallback)
            ? call_user_func($this->countCallback, $this)
            : $this->iterator->count();
    }

    /**
     * Gets the key for the current position of collection
     *
     * @return mixed
     */
    public function key()
    {
        return $this->generateValueKey($this->current());
    }

    /**
     * Checks the existence of a key in the collection
     *
     * @param $key
     *
     * @return bool
     */
    public function has($key): bool
    {
        return $this->iterator->offsetExists($key);
    }

    /**
     * Gets the collection object by key
     *
     * @param $key
     *
     * @return object|mixed
     */
    public function get($key)
    {
        return $this->convertValue(
            $this->iterator->offsetGet($key)
        );
    }

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
    public function getAll(): array
    {
        $result = [];
        $this->rewind();

        foreach ($this as $value) {
            $value = $this->convertValue($value);
            $key = $this->generateValueKey($value);

            if ($key === null) {
                throw new CollectionException('Invalid generated key is NULL');
            }

            $result[$key] = $value;
        }

        return $result;
    }

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
    public function set($key, $value): void
    {
        $value = $this->convertValue($value);
        $key = $key ?? $this->generateValueKey($value);

        if (!$this->check($key, $value)) {
            throw new CollectionException(
                'Invalid set collection "' . static::class . "\" value with key \"{$key}\""
            );
        }

        $this->iterator->offsetSet($key, $value);
    }

    /**
     * Sets the list of objects in the collection
     *
     * @param array $items
     */
    public function setAll(array $items): void
    {
        foreach ($items as $item) {
            $this->add($item);
        }
    }

    public function add($value): void
    {
        $this->set(null, $value);
    }

    /** @inheritDoc */
    public function remove($key)
    {
        $this->iterator->offsetUnset($key);
    }

    public function removeAll(): void
    {
        $this->iterator = new ArrayIteratorIterator(new ArrayIterator());
    }

    /** @inheritDoc */
    public function current()
    {
        return $this->convertValue(
            $this->iterator->current()
        );
    }

    /** @inheritDoc */
    public function next(): void
    {
        $this->iterator->next();
    }

    /** @inheritDoc */
    public function rewind(): void
    {
        $this->iterator->rewind();
    }

    /** @inheritDoc */
    public function valid(): bool
    {
        return $this->iterator->valid();
    }

    /** @inheritDoc */
    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    /** @inheritDoc */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /** @inheritDoc */
    public function offsetSet($offset, $value): void
    {
        $this->set($offset, $value);
    }

    /** @inheritDoc */
    public function offsetUnset($offset): void
    {
        $this->remove($offset);
    }

    /**
     * Converts the collection to an array
     *
     * @throws \Wmsamolet\PhpCollections\CollectionException
     *
     * @return array
     */
    public function toArray(): array
    {
        $result = [];

        foreach ($this->getAll() as $k => $v) {
            if (
                is_subclass_of($v, Arrayable::class)
                ||
                is_subclass_of($v, CollectionInterface::class)
            ) {
                $result[$k] = $v->toArray();
            }
        }

        return $result;
    }

    /**
     * Converts a value when setting and getting data from the collection
     *
     * @param $value
     *
     * @return object
     */
    protected function convertValue($value)
    {
        return $value;
    }
}