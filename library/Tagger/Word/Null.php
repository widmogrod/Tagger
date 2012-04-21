<?php
namespace Tagger\Word;

/**
 * @author gabriel
 */

class Null extends Word
{
    public function __construct($word)
    {}

    public function __toString()
    {
        return "\x0";
    }

    public function getLength()
    {
        return 0;
    }

    public function setPriority($priority)
    {}

    public function getPriority()
    {
        return 0;
    }
}