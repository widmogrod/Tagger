<?php
/**
 * @author gabriel
 */
 
namespace Tagger\Strategy;

use Tagger\Options,
    Tagger\Word,
    Tagger\WordQueue,
    Tagger\WordPriority,
    Tagger\PriorityFilterIterator,
    Tagger\Strategy as StrategyInterface;

class PlainText implements StrategyInterface
{
    /**
     * @var \Tagger\Options
     */
    protected $options;

    /**
     * @var \SplQueue
     */
    protected $wordlist;

    /**
     * @var \SplPriorityQueue
     */
    protected $wordPriority;

    public function __construct(Options $options)
    {
        $this->options = $options;
    }

    public function retrieveTags($data)
    {
        $data = $this->striptags($data);
        $data = $this->htmltrim($data);

        $words = explode(' ', $data);
        $words = array_filter($words, function($value){
            return preg_replace('/([^\pL\pN]+)/ui', null, $value);
        });

        $this->wordlist = new WordQueue();
        $words = array_map(array($this, 'addWord'), $words);

        $wordsPriority = array_count_values($words);
        asort($wordsPriority, SORT_STRING);
        var_dump($wordsPriority);

        $wp = new WordPriority($this->options);
        foreach ($wordsPriority as $word => $priority) {
            $wp->insert(new Word($word), $this->options->getPriority($word) * $priority);
        }

        //$wp->rewind();
        $it = new \LimitIterator($wp, 0, 5);
        $it->rewind();

//        $wq = new WordQueue();
//        $wp = new WordPriority($this->options);
        $wq = $this->wordlist;

        while ($it->valid())
        {
            $word = $it->current();

            $pi = new PriorityFilterIterator($wq, $wp, $word, 2);
            $iti = new \LimitIterator($pi, 0, 3);
            $iti->rewind();

            while ($iti->valid())
            {
                $wordsIt = $iti->current();
                $wordsIt->rewind();
                echo "\n $word ";
                while ($wordsIt->valid()) {
                    echo $wordsIt->current();
                    echo " ";
                    $wordsIt->next();
                }
                echo "\n";

                $iti->next();
            }
            $it->next();
        }

        //return $this->wordPriority;
//        return $this->wordlist;
    }

    protected function htmltrim($string)
    {
        $pattern = '(?:[\t\n\r\x0B\x00\x{A0}\x{AD}\x{2000}-\x{200F}\x{201F}\x{202F}\x{3000}\x{FEFF}]|&nbsp;|<br\s*\/?>)+';
        return preg_replace('/' . $pattern . '/u', ' ', $string);
    }

    protected function striptags($string)
    {
        $string = preg_replace('#(<(style|script)[^>]*>[^<]*(</style>|</script>))#i', ' ', $string);
        $string = strip_tags($string);
        return $string;
    }

    protected function filterword($string)
    {
        $string = preg_replace('/([^\pL\pN]+)/ui', ' ', $string);
        $string = trim($string);
        $string = mb_strtolower($string);
        return $string;
    }

    protected function addWord($word)
    {
        $word = $this->filterword($word);
        $this->wordlist->enqueue(new Word($word));
        return $word;
    }
}