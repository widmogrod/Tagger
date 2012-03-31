<?php
/**
 * @author gabriel
 */
 
namespace Tagger;

class Word
{
    protected $word;

    protected $slug;

    public function __construct($word)
    {
        $this->word = $word;
    }

    public function __toString()
    {
        return $this->word;
    }

    public function similarity(Word $word)
    {
        return strcasecmp($this, $word);
    }
}