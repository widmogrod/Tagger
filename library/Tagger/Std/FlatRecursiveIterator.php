<?php
namespace Tagger\Std;

/**
 * @author gabriel
 */
class FlatRecursiveIterator implements \RecursiveIterator
{
    protected $iterator;

    public function __construct(\Iterator $iterator)
    {
        $this->iterator = $iterator;
    }

    public function hasChildren()
    {
        return false;
    }

    public function getChildren()
    {
        // throw exception?
    }

    public function current()
    {
        return $this->iterator->current();
    }

    public function next()
    {
        $this->iterator->next();
    }

    public function key()
    {
        return $this->iterator->key();
    }

    public function valid()
    {
        return $this->iterator->valid();
    }

    public function rewind()
    {
        $this->iterator->rewind();
    }

}
