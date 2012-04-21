<?php
namespace Tagger\Word;

use Tagger\Word as WordInterface;

/**
 * @author gabriel
 */

class Word implements WordInterface
{
    protected $word;
    protected $prev;
    protected $next;
    protected $length;
    protected $priority;

    public function __construct($word)
    {
        $this->word = $word;
        $this->length = mb_strlen($word);
    }

    public function __toString()
    {
        return (string) $this->word;
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
//        var_dump('setPrev');
        $this->prev = $word;

        if ($connect) {
            $word->setNext($this, false);
        }
    }

    public function getPrev()
    {
        if (null === $this->prev) {
//            var_dump('getPrev::null');
            $this->setPrev(new Null(null));
        }
        return $this->prev;
    }

    public function setNext(WordInterface $word, $connect = true)
    {
//        var_dump('setNext');
        $this->next = $word;
        if ($connect) {
            $word->setPrev($this, false);
        }
    }

    public function getNext()
    {
        if (null === $this->next) {
//            var_dump('getNext::null');
            $this->setNext(new Null(null));
        }
        return $this->next;
    }
}