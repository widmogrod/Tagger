<?php
namespace Tagger\Word;

use Tagger\Word as WordInterface;

/**
 * @author gabriel
 */

class Word implements WordInterface
{
    protected $word;
    protected $hash;
    protected $prev;
    protected $next;
    protected $length;
    protected $priority;

    public function __construct($word)
    {
        $this->word = $word;
        $this->hash = mb_strtolower($word);
        $this->length = mb_strlen($word);
    }

    public function __toString()
    {
        return (string) $this->word;
    }

    public function isSame(WordInterface $word)
    {
        return $this->getHash() == $word->getHash();
    }

    public function getHash()
    {
        return $this->hash;
    }

    public function getLength()
    {
        return $this->length;
    }

    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    public function getPriority()
    {
        return $this->priority;
    }

    public function setPrev(WordInterface $word, $connect = true)
    {
        $this->prev = $word;
        if ($connect) {
            $word->setNext($this, false);
        }
    }

    public function getPrev()
    {
        if (null === $this->prev) {
            $this->setPrev(new Null(null));
        }
        return $this->prev;
    }

    public function setNext(WordInterface $word, $connect = true)
    {
        $this->next = $word;
        if ($connect) {
            $word->setPrev($this, false);
        }
    }

    public function getNext()
    {
        if (null === $this->next) {
            $this->setNext(new Null(null));
        }
        return $this->next;
    }
}