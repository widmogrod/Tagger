<?php
namespace Tagger\Collector;

use Tagger\WordGroup\Base,
    Tagger\Word,
    Tagger\Std as Std;

/**
 * @author gabriel
 */
class Similar
{
    protected $iterator;

    protected $similarity = array();
    protected $mark = array();

    protected $minWordLength = 3;

    protected $minSimilarityPercent = 79;

    public function __construct(\Iterator $iterator)
    {
        $this->iterator = $iterator;
    }

    public function getGroupedWords()
    {
        $a = new \ArrayObject(array());

        $iterator = new Std\CacheIterator($this->iterator);
//        $iterator->rewind();
        foreach ($iterator as $position => /** @var $word Word */ $word)
        {
            $length = $word->getLength();

            if ($length <= $this->minWordLength) {
                continue;
            }

            if ($this->is($word)) {
                continue;
            }

            $similarityIterator = new Std\CallbackFilterIterator(
                $iterator,
                function(Word $current) use ($word)
                {
                    if ($word->isSame($current)) {
                        return true;
                    }

                    $baseLength = $word->getLength();
                    $currentLength = $current->getLength();

                    $minLength = $currentLength < $baseLength ? $currentLength : $baseLength;
                    $toLength = $minLength;

                    return mb_substr($current, 0, $toLength) == mb_substr($word, 0, $toLength);
                }
            );

            foreach($similarityIterator as /** @var $testWord Word */ $testWord)
            {
                if ($word->isSame($testWord)) {
                    $this->setWordSimilarTo($word, $testWord);
                    continue;
                }

                if ($testWord->getLength() < $this->minWordLength) {
                    continue;
                }

                similar_text((string) $word, (string) $testWord, $percent);

                if ($percent < $this->minSimilarityPercent) {
                    continue;
                }

                $this->setWordSimilarTo($word, $testWord);
//                $this->setWordSimilarTo($testWord, $word);
            }

            $iterator->seek($position);
        }

        return new \ArrayIterator($this->similarity);
    }

    public function setWordSimilarTo(Word $word, Word $similarWord)
    {
        if (!isset($this->similarity[$word->getHash()])) {
            $this->similarity[$word->getHash()] = new \Tagger\WordGroup\Base();
        }
        $this->similarity[$word->getHash()]->addWord($similarWord);

        $this->mark[$word->getHash()] = true;
        $this->mark[$similarWord->getHash()] = true;
    }

    public function is(Word $word)
    {
        return isset($this->mark[$word->getHash()]);
    }
}
