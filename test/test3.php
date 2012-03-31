<?php
namespace
{
    /**
     * Setup
     */
    set_include_path(implode(PATH_SEPARATOR, array(
        __DIR__ .'/../library'
    )));

    spl_autoload_register(function($className){
        require_once str_replace('\\','/', $className) . '.php';
    });
}

namespace Tagger
{
    class PlainText
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

        public function retrieveTags($data)
        {
            $data = $this->striptags($data);
            $data = $this->htmltrim($data);

            $words = explode(' ', $data);
            $words = array_filter($words, function($value){
                return preg_replace('/([^\pL\pN]+)/ui', null, $value);
            });

            $words = array_map(array($this, 'filterword'), $words);

//            $wordsPriority = array_count_values($words);
//            asort($wordsPriority, SORT_STRING);
//             var_dump($wordsPriority);
            return $words;
        }
    }

    class Similarity
    {
        protected $minWordLength = 3;
        protected $minSimilarityPercent = 79;
        protected $similarity = array();
        protected $notSimilar = array();

        public function __construct(array $words)
        {
            //echo "slowo;iteracja;czas;word_to_compare;comparsions\n";

            $i = 0;
            while ($word = array_pop($words))
            {
                if (mb_strlen($word) <= $this->minWordLength) {
                    continue;
                }

//                $similarityComp = 0;
//                $start = microtime(true);
//                $back = array();
//                while($testWord = array_shift($words))
                foreach($words as $testWord)
                {
                    if ($word == $testWord) {
                        continue;
                    }
                    if (mb_strlen($testWord) <= $this->minWordLength) {
                        continue;
                    }

//                    ++$similarityComp;
                    similar_text($word, $testWord, $percent);

//                    $back[] = $testWord;

                    if ($percent < $this->minSimilarityPercent) {
                        //$this->setWordNotSimilarTo($word, $testWord);
                        //$this->setWordNotSimilarTo($testWord, $word);
                        continue;
                    }

                    $this->setWordSimilarTo($word, $testWord);
                    $this->setWordSimilarTo($testWord, $word);
                }
//                $stop = microtime(true);
//                printf("%s;%d;%s;%d;%d\n", $word, ++$i, number_format($stop-$start, 10, ',',''), sizeof($words), $similarityComp);

//                $words = $back;
            }
        }

        public function setWordSimilarTo($word, $similarWord)
        {
            if (!isset($this->similarity[$word])) {
                $this->similarity[$word] = array();
            }
            $this->similarity[$word][$similarWord] = true;
        }

        public function setWordNotSimilarTo($word, $similarWord)
        {
            if (!isset($this->notSimilar[$word])) {
                $this->notSimilar[$word] = array();
            }
            $this->notSimilar[$word][$similarWord] = true;
        }

        public function getSimilarWordsTo($word, $default = array())
        {
            return array_key_exists($word, $this->similarity)
                ? $this->similarity[$word]
                : $default;
        }

        public function toArray()
        {
            return $this->similarity;
        }
    }

    class Mediator
    {
        protected $words;
    }

    class Word
    {
        protected $word;

        protected $nextWord;

        protected $position;

        public function __construct($word)
        {
            $this->word = $word;
        }

        public function __toString()
        {
            return $this->word;
        }
    }

    /**
     * Test
     */

    $excludeWords = file(__DIR__ .'/_data/polskiespojniki.txt');
//    $html = file_get_contents(__DIR__ . '/_data/laksa.html');
    $html = file_get_contents(__DIR__ . '/_data/blog.widmogrod.html');
//    $html = file_get_contents(__DIR__ . '/_data/andredom.pl.html');

    $encoding = mb_detect_encoding($html);
    $oldEncoding = mb_internal_encoding();
    mb_internal_encoding($encoding);

    $html = html_entity_decode($html, null, $encoding);

    $options = new Options();
    $options->setPriorityForNumber(5);
    $options->setPriorityForString(20);
    $options->setPriorityForStingLength(1, 0);
    $options->setPriorityForStingLength(2, 2);
    $options->setPriorityForStingLength(3, 4);
    $options->setPriorityForStingLength(4, 8);
    $options->setPriorityForStingLength(5, 16);



    $t = new PlainText($options);
    $words = $t->retrieveTags($html);
    // var_dump($words);

    $words = array_count_values($words);
    $words = array_keys($words);

    $start = microtime(true);
    $s = new Similarity($words);
    $similar = $s->toArray();
    var_dump($similar);
//
    var_dump(microtime(true) - $start);


//    $excludeWords = array_map('trim', $excludeWords);
////    var_dump($excludeWords);
//
//    $summary = array_count_values($words);
//    asort($summary, SORT_STRING);
//    // var_dump($summary);
//
//    $priorityQueue = new \SplPriorityQueue();
//    foreach ($summary as $word => $occurrences)
//    {
//        $priority = 0;
//        if (!in_array($word, $excludeWords)) {
//            $priority = $occurrences;
//        }
//
//        $similarWords = $s->getSimilarWordsTo($word);
//        $excludedBySimilarity = array_intersect($excludeWords, $similarWords);
//        if (!$excludedBySimilarity)
//        {
//            $priority += count($similarWords);
//            $priority *= $options->getPriority($word);
//            //$priority = pow($priority, $options->getPriority($word));
//            //$priority += array_sum(array_map(array($options,'getPriority'), $similarWords));
//            //$priority += count($similarWords);
//            //$priority = log($priority);
//            //var_dump($priority);
//        }
//
//        //var_dump(array($word, $priority));
//        $priorityQueue->insert($word, $priority);
//    }
//
//    foreach ($priorityQueue as $word) {
//        var_dump($word);
//    }



}