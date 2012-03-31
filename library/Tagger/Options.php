<?php
/**
 * @author gabriel
 */

namespace Tagger;

class Options
{
    protected $_numberPriority;
    protected $_numberLangthPriority = array();

    protected $_stringPriority;
    protected $_stringLangthPriority = array();

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

    public function getPriority($value)
    {
        $value = (string) $value;
        $length = mb_strlen($value);
        return is_numeric($value)
            ? $this->getPriorityForNumber($length)
            : $this->getPriorityForString($length);
    }
}
