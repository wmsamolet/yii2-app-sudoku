<?php

namespace Wmsamolet\PhpCollections;

use ArrayAccess;
use Countable;
use Iterator;
use OuterIterator;

class ArrayIteratorIterator implements OuterIterator, ArrayAccess, Countable
{
    /** @var \Traversable */
    private $innerIterator;

    protected $data;

    public function __construct(Iterator $iterator)
    {
        $this->innerIterator = $iterator;
    }

    /**
     * Returns the inner iterator for the current entry.
     *
     * @link https://php.net/manual/en/outeriterator.getinneriterator.php
     * @return \Iterator The inner iterator for the current entry.
     * @since 5.1.0
     */
    public function getInnerIterator()
    {
        return $this->innerIterator;
    }

    /**
     * Return the current element
     *
     * @link https://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current()
    {
        $this->processData();

        return current($this->data);
    }

    /**
     * Move forward to next element
     *
     * @link https://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next(): void
    {
        $this->processData();

        next($this->data);
    }

    /**
     * Return the key of the current element
     *
     * @link https://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key()
    {
        $this->processData();

        return key($this->data);
    }

    /**
     * Checks if current position is valid
     *
     * @link https://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid(): bool
    {
        $this->processData();

        return key($this->data) !== null;
    }

    /**
     * Rewind the Iterator to the first element
     *
     * @link https://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind(): void
    {
        $this->processData();

        reset($this->data);
    }

    /**
     * Count elements of an object
     *
     * @link https://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count(): int
    {
        $this->processData();

        return count($this->data);
    }

    /**
     * Whether a offset exists
     *
     * @link https://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     *
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset): bool
    {
        $this->processData();

        return array_key_exists($offset, $this->data);
    }

    /**
     * Offset to retrieve
     *
     * @link https://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     *
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        $this->processData();

        return $this->data[$offset];
    }

    /**
     * Offset to set
     *
     * @link https://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param int|string $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     *
     * @return mixed
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        $this->processData();

        $this->data[$offset] = $value;

        return $value;
    }

    /**
     * Offset to unset
     *
     * @link https://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param int|string $offset <p>
     * The offset to unset.
     * </p>
     *
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset): void
    {
        $this->processData();

        unset($this->data[$offset]);

        // \yii\helpers\VarDumper::dump(['UNSET' => [$offset => $this->data]], 4);
    }

    /**
     * Handles (saves) the data of the iterator memory object
     *
     * @return void
     */
    protected function processData(): void
    {
        if ($this->data === null) {
            $this->data = [];
            $this->innerIterator->rewind();

            foreach ($this->innerIterator as $k => $v) {
                /** @noinspection OffsetOperationsInspection */
                $this->data[$k] = $v;
            }

            reset($this->data);
        }
    }
}