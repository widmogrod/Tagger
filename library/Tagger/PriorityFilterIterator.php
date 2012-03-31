<?php
/**
 * @author gabriel
 */
 
namespace Tagger;

class PriorityFilterIterator extends \SplHeap implements \OuterIterator
{
    /**
     * @var WordQueue
     */
    protected $iterator;

    /**
     * @var WordPriority
     */
    protected $priority;

    /**
     * @var Word
     */
    protected $filter;

    public function __construct(WordQueue $iterator, WordPriority $priority, Word $filter, $count)
    {
        $this->iterator = $iterator;
        $this->priority = $priority;
        $this->filter = $filter;
        $this->count = abs((int) $count);
    }

    /**
     * @return boolean
     */
    public function accept()
    {
        return $this->filter->similarity($this->getInnerIterator()->current()) == 0;
    }

    /**
     * Compare priorities in order to place elements correctly in the heap while shifting up.
     *
     * @param Word $word1
     * @param Word $word2
     * @return int
     */
    public function compare($word1, $word2)
    {
        return $this->priority->compare($word1, $word2);
    }

    public function rewind()
    {
        $count = 0;
        $words = null;

        $iterator = $this->getInnerIterator();
        $iterator->rewind();
        while ($iterator->valid())
        {
            if ($this->accept())
            {
                $current = $iterator->current();
                $this->insert($words);

                $count = 0;
                $words = new WordQueue();
            }
            elseif ($words && $count < $this->count)
            {
                ++$count;
                $words->enqueue($iterator->current());
            }
            $iterator->next();
        }

        parent::rewind();
    }


    /**
     * @return WordQueue
     */
    public function getInnerIterator()
    {
        return $this->iterator;
    }
}