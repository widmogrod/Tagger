<?php
namespace Tagger\Std;

/**
 * @author ghabryn
 */
class DOMNodeListRecursiceIterator implements \RecursiveIterator, \Countable
{
    /**
     * @var \DOMNodeList
     */
    protected $list;

    /**
     * @var int
     */
    protected $count;

    /**
     * @var int
     */
    protected $position;

    public function __construct(\DOMNodeList $list)
    {
        $this->list = $list;
        $this->count = $list->length;
        $this->position = 0;
    }

    public function current()
    {
        return $this->list->item($this->position);
    }

    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        ++$this->position;
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function valid()
    {
        return $this->count > $this->position;
    }

    public function getChildren()
    {
        return new self($this->current()->childNodes);
    }

    public function hasChildren()
    {
        return $this->current()->hasChildNodes();
    }

    public function count()
    {
        return $this->count;
    }
}