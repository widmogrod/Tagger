<?php
namespace Tagger;

/**
 * @author gabriel
 */
interface Word
{
    public function __construct($word);

    public function __toString();

    public function getLength();

    public function setPriority($priority);

    public function getPriority();

    public function setPrev(Word $word);

    public function getPrev();

    public function setNext(Word $word);

    public function getNext();

    public function getHash();

    public function isSame(Word $word);
}
