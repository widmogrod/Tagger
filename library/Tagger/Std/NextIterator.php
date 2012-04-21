<?php
namespace Tagger\Std;

/**
 * @author gabriel
 */
class NextIterator implements \Iterator
{
    /**
     * @var \Iterator
     */
    protected $iterator;

    public function __construct(\SeekableIterator $iterator)
    {
        $this->iterator = $iterator;
    }

    public function current()
    {
        return $this->iterator->current();
    }

    public function key()
    {
        return $this->iterator->key();
    }

    public function next()
    {
        $this->iterator->next();
    }

    public function rewind()
    {
        $this->iterator->rewind();
    }

    public function valid()
    {
        return $this->iterator->valid();
    }

    public function hasNext()
    {
        $key = $this->iterator->key();
        $this->iterator->next();
        $hasNext = $this->iterator->valid();
        $this->iterator->seek($key);
        return $hasNext;
    }

    public function getNext()
    {
        $key = $this->iterator->key();
        $this->iterator->next();
        $next = $this->iterator->current();
        $this->iterator->seek($key);
        return $next;
    }
}
