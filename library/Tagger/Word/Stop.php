<?php
namespace Tagger\Word;

use Tagger\Word;

/**
 * @author gabriel
 */

class Stop implements Word
{
    protected $word;
    protected $length;

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
    {}

    public function getPriority()
    {
        return 0;
    }
}