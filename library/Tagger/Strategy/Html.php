<?php
namespace Tagger\Strategy;

use Tagger\Word as Word,
    Tagger\Strategy,
    Tagger\Priority as Priority;

/**
 * @author gabriel
 */
class Html implements Strategy
{
    const WORD_DELIMETER = '/[\s,]+/';

    protected $priority;

    public function __construct(Priority $priority)
    {
        $this->priority = $priority;
    }

    public function canExtractWords($value)
    {
        if (!$value instanceof \DOMNode) {
            return false;
        }

        /** @var $value \DOMNode */
        $phrase = $value->nodeValue;
        $phrase = trim($phrase);
        if (empty($phrase)) {
            return false;
        }

        return (false !== preg_match(self::WORD_DELIMETER, $phrase));
    }

    public function extractWords($value)
    {
        $phrase = $value->nodeValue;
        $words = preg_split(self::WORD_DELIMETER, $phrase);
//        $words = array_filter($words, function($value){
//            return preg_replace('/([^\pL\pN]+)/ui', null, $value);
//        });
        $words = array_map(array($this, 'filterword'), $words);

        $basePriority = $this->priority->getPriority($value);
        $result = array();

        foreach ($words as $word)
        {
            $word = $this->createWord($word);
            $word->setPriority($this->priority->getPriority($word) + $basePriority);
            $result[] = $word;
        }

        return $result;
    }

    protected function filterword($string)
    {
        $string = preg_replace('/[^\pL\pN]+/ui', ' ', $string);
        $string = trim($string);
        $string = mb_strtolower($string);
        return $string;
    }

    /**
     * @param $value
     * @return Word
     * @throws \Exception
     */
    public function createWord($value)
    {
        if ($value instanceof \DOMNode) {
            $value = $value->nodeValue;
        }

        return new Word\Word($value);
    }
}