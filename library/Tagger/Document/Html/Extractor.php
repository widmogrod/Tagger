<?php
namespace Tagger\Document\Html;

use Tagger\Strategy,
    Tagger\Std\FlatRecursiveIterator;

/**
 * @author gabriel
 */

class Extractor implements \RecursiveIterator
{
    /**
     * @var \Iterator
     */
    protected $iterator;

    /**
     * @var Strategy
     */
    protected $strategy;

    protected $cached = array();

    public function __construct(\Iterator $iterator, Strategy $strategy)
    {
        $this->iterator = $iterator;
        $this->strategy = $strategy;
    }

    /**
     * @return Word
     */
    public function current()
    {
        return isset($this->cached[$this->key()])
            ? $this->cached[$this->key()]
            : ($this->cached[$this->key()] = $this->strategy->createWord($this->iterator->current()));
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

    public function getChildren()
    {
        $words = $this->strategy->extractWords($this->iterator->current());
        return new FlatRecursiveIterator(new \ArrayIterator($words));
    }

    public function hasChildren()
    {
        return $this->strategy->canExtractWords($this->iterator->current());
    }
}
