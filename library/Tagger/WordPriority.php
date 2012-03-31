<?php
/**
 * @author gabriel
 */
 
namespace Tagger;

use Tagger\Word;

class WordPriority extends \SplPriorityQueue
{
    public function priority(Word $word)
    {
//        $this->rewind();
//        while ($this->valid()) {
//            $current = $this->current();
//            if ($word->similarity($current['data'])) {
//                return $current['priority'];
//            }
//        }
    }
//    protected $options;

//    public function __construct(Options $options)
//    {
//        //parent::__construct();
//        $this->options = $options;
//    }
//
//    /**
//     * Result of the comparison:
//     * - positive integer if priority of $word1 is greater than $word2,
//     * - 0 if they are equal,
//     * - negative integer otherwise.
//     *
//     * @param Word $word1
//     * @param Word $word2
//     * @return int
//     */
//    public function compare($word1, $word2)
//    {
//        $p1 = $this->options->getPriority($word1);
//        $p2 = $this->options->getPriority($word2);
//
//        if ($p1 == $p1) {
//            return 0;
//        }
//
//        return ($p1 > $p2) ? 1 : -1;
//    }1
}