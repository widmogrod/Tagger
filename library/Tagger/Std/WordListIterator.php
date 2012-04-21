<?php
namespace Tagger\Std;

use Tagger\Extractor,
    Tagger\Word;

/**
 * @author gabriel
 */
class WordListIterator  implements \RecursiveIterator
{
    /**
     * @var \Iterator
     */
    protected $iterator;

    /**
     * @var Extractor
     */
    protected $extractor;

    protected $cached = array();

    public function __construct(\Iterator $iterator, Extractor $extractor)
    {
        $this->iterator = $iterator;
        $this->extractor = $extractor;
    }

    /**
     * @return Word
     */
    public function current()
    {
        return isset($this->cached[$this->key()])
            ? $this->cached[$this->key()]
            : ($this->cached[$this->key()] = $this->extractor->createWord($this->iterator->current()));
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
        $words = $this->extractor->extractWords($this->iterator->current());
        return new self($words, $this->extractor);
    }

    public function hasChildren()
    {
        return $this->extractor->canExtractWords($this->iterator->current());
    }
}
