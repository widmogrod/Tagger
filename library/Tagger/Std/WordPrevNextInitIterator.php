<?php
namespace Tagger\Std;

use Tagger\Word,
    Tagger\Std\NextIterator;

/**
 * @author gabriel
 */
class WordPrevNextInitIterator  implements \Iterator
{
    /**
     * @var NextIterator
     */
    protected $iterator;

    public function __construct(NextIterator $iterator)
    {
        $this->iterator = $iterator;
    }

    /**
     * @return Word
     */
    public function current()
    {
        /** @var $word Word */
        $word = $this->iterator->current();
        if ($this->iterator->hasNext()) {
            $word->setNext($this->iterator->getNext());
        }
        return $word;
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
        $this->prev = null;
    }

    public function valid()
    {
        return $this->iterator->valid();
    }
}
