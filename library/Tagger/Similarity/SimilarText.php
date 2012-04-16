<?php
namespace Tagger\Similarity;

use Tagger\Similarity;

/**
 * @author gabriel
 */
class SimilarText implements Similarity
{
    protected $minWordLength = 3;

    protected $minSimilarityPercent = 79;

    protected $similarity = array();

    public function __construct(array $words)
    {
        while ($word = array_pop($words))
        {
            if (mb_strlen($word) <= $this->minWordLength) {
                continue;
            }

            foreach($words as $testWord)
            {
                if ($word == $testWord) {
                    continue;
                }
                if (mb_strlen($testWord) <= $this->minWordLength) {
                    continue;
                }

                similar_text($word, $testWord, $percent);

                if ($percent < $this->minSimilarityPercent) {
                    continue;
                }

                $this->setWordSimilarTo($word, $testWord);
                $this->setWordSimilarTo($testWord, $word);
            }
        }
    }

    public function setWordSimilarTo($word, $similarWord)
    {
        if (!isset($this->similarity[$word])) {
            $this->similarity[$word] = array();
        }
        $this->similarity[$word][$similarWord] = true;
    }

    public function getSimilarWordsTo($word, $default = array())
    {
        return array_key_exists($word, $this->similarity)
            ? $this->similarity[$word]
            : $default;
    }

    public function toArray($flatWords = false)
    {
        if ($flatWords)
        {
            $result = array();
            foreach ($this->similarity as $word => $similarWords)
            {
                $result[] = $word;
                $result = array_merge($result, $similarWords);
            }
            return $result;
        }

        return $this->similarity;
    }

    public function setMinWordLength($minWordLength)
    {
        $this->minWordLength = $minWordLength;
    }

    public function getMinWordLength()
    {
        return $this->minWordLength;
    }

    public function setMinSimilarityPercent($minSimilarityPercent)
    {
        $this->minSimilarityPercent = $minSimilarityPercent;
    }

    public function getMinSimilarityPercent()
    {
        return $this->minSimilarityPercent;
    }
}
