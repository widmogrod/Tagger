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
}
