<?php
namespace Tagger\Word;

use Tagger\Word as WordInterface;

/**
 * @author gabriel
 */

class Word implements WordInterface
{
    protected $word;
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
}