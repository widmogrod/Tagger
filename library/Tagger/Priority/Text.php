<?php
/**
 * @author gabriel
 */

namespace Tagger\Priority;

use Tagger\Priority;

class Text implements Priority
{
    protected $_numberPriority;

    protected $_numberLangthPriority = array();

    protected $_stringPriority;

    protected $_stringLangthPriority = array();

    protected $_blackList = array();

    protected $_priorityList = array();

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

    public function addWordsToBlackList(array $words)
    {
        array_map(array($this, 'addWordToBlackList'), $words);
    }

    public function addWordToBlackList($word)
    {
        $this->_blackList[$word] = true;
    }

    public function addWordsToPriorityList(array $words, $priority)
    {
        while ($word = array_pop($words)) {
            $this->addWordToPriorityList($word, $priority);
        }
    }

    public function addWordToPriorityList($word, $priority)
    {
        $this->_priorityList[$word] = abs((int) $priority);
    }

    public function getPriority($value)
    {
        $value = (string) $value;
        $length = mb_strlen($value);

        if (isset($this->_blackList[$value])) {
            return 0;
        }
        if (isset($this->_priorityList[$value])) {
            return $this->_priorityList[$value];
        }

        return is_numeric($value)
            ? $this->getPriorityForNumber($length)
            : $this->getPriorityForString($length);
    }
}