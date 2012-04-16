<?php

namespace Tagger\Priority;

use Tagger\Priority;

/**
 * @author gabriel
 */
class Html implements Priority
{
    const WORD_TOTAL_PRIORITY = '__all__';
    const DEFAULT_PRIORITY = 1;

    protected $_wordPriority = array();

    protected $_tagNamePriority = array();

    protected $_blackListWords = array();

    protected $_numberPriority;

    protected $_numberLangthPriority = array();

    protected $_stringPriority;

    protected $_stringLangthPriority = array();

    public function getPriority($word)
    {
        $word = mb_strtolower($word);
        if (isset($this->_blackListWords[$word])) {
            return 0;
        }

        if (isset($this->_wordPriority[$word])) {
            return $this->_wordPriority[$word][self::WORD_TOTAL_PRIORITY];
        }

        $length = mb_strlen($word);

        return is_numeric($word)
            ? $this->getPriorityForNumber($length)
            : $this->getPriorityForString($length);
    }

    public function setTagNamePriority($tagName, $priority)
    {
        $tagName = strtolower($tagName);
        $this->_tagNamePriority[$tagName] = (int) $priority;
    }

    public function getTagNamePriority($tagName)
    {
        $tagName = strtolower($tagName);
        return array_key_exists($tagName, $this->_tagNamePriority)
            ? $this->_tagNamePriority[$tagName]
            : self::DEFAULT_PRIORITY;
    }

    public function addWordsForTag(array $words, $tagName)
    {
        while ($word = array_pop($words)) {
            $this->addWordForTag($word, $tagName);
        }
    }

    public function addWordForTag($word, $tagName)
    {
        $word = mb_strtolower($word);
        if (!array_key_exists($word, $this->_wordPriority)) {
            $this->_wordPriority[$word] = array(
                self::WORD_TOTAL_PRIORITY => self::DEFAULT_PRIORITY
            );
        }

        $priority = $this->getTagNamePriority($tagName);
        $this->_wordPriority[$word][$tagName] = $priority;
        $this->_wordPriority[$word][self::WORD_TOTAL_PRIORITY] += $priority;
    }

    public function addWordsToBlackList(array $words)
    {
        array_map(array($this, 'addWordToBlackList'), $words);
    }

    public function addWordToBlackList($word)
    {
        $this->_blackListWords[$word] = true;
    }

    public function setPriorityForNumber($priority)
    {
        $this->_numberPriority = (int) $priority;
    }

    public function setPriorityForNumerLength($length, $priority)
    {
        $this->_numberLangthPriority[abs((int) $length)] = (int) $priority;
    }

    public function setPriorityForString($priority)
    {
        $this->_stringPriority = $priority;
    }

    public function setPriorityForStingLength($length, $priority)
    {
        $this->_stringLangthPriority[abs((int) $length)] = (int) $priority;
    }

    public function getPriorityForNumber($length = null)
    {
        return ($length > 0 && array_key_exists($length, $this->_numberLangthPriority))
            ? $this->_numberLangthPriority[$length]
            : $this->_numberPriority;
    }

    public function getPriorityForString($length = null)
    {
        return ($length > 0 && array_key_exists($length, $this->_stringLangthPriority))
            ? $this->_stringLangthPriority[$length]
            : $this->_stringPriority;
    }
}