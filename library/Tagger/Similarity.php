<?php
/**
 * @author gabriel
 */
 
namespace Tagger;

interface Similarity
{
    public function getSimilarWordsTo($word);
}