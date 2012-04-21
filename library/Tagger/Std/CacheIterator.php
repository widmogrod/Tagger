<?php
namespace Tagger\Std;

use Tagger\Exception\OutOfBoundsException;

/**
 * @author gabriel
 */
class CacheIterator implements \Iterator, \Countable, \SeekableIterator
{
    const CACHE_KEY = 0;
    const CACHE_VALUE = 1;

    protected $cache = array();
    protected $count = 0;
    protected $position = 0;

    public function __construct(\Iterator $iterator)
    {
        foreach ($iterator as $key => $value)
        {
            ++$this->count;
            $this->cache[] = array(
                self::CACHE_KEY => $key,
                self::CACHE_VALUE => $value
            );
        }
    }

    public function current()
    {
        return $this->cache[$this->position][self::CACHE_VALUE];
    }

    public function next()
    {
        return ++$this->position;
    }

    public function key()
    {
        return $this->position;
    }

    public function valid()
    {
        return $this->count > $this->position;
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function count()
    {
        return $this->count;
    }

    public function seek($position)
    {
        if (!isset($this->cache[$position])) {
            throw new OutOfBoundsException("invalid seek position ($position)");
        }
        $this->position = $position;
    }

    public function __clone()
    {
        //
    }
}
