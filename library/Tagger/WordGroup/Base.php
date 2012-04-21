<?php
namespace Tagger\WordGroup;

use Tagger\WordGroup,
    Tagger\Word;

/**
 * @author gabriel
 */
class Base implements WordGroup
{
    protected $words = array();

    public function addWord(Word $word)
    {
        $this->words[] = $word;
    }

    public function getWords()
    {
        return $this->words;
    }

    public function getOccurrences()
    {
        return count($this->words);
    }
}
