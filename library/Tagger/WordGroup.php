<?php
namespace Tagger;

use Tagger\Word;

/**
 * @author gabriel
 */
interface WordGroup
{
    public function addWord(Word $word);
    public function getWords();
    public function getOccurrences();
}
