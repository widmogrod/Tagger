<?php
namespace Tagger\Collector;

use Tagger\WordGroup\Base;

/**
 * @author gabriel
 */
class Same
{
    protected $iterator;

    public function __construct(\Iterator $iterator)
    {
        $this->iterator = $iterator;
    }

    public function getGroupedWords()
    {
        $a = new \ArrayObject(array());
        foreach ($this->iterator as $word)
        {
            $key = $word->getHash();
            if (!$a->offsetExists($key)) {
                $a->offsetSet($key, new Base());
            }
            $a->offsetGet($key)->addWord($word);
        }
        return $a->getIterator();
    }
}
