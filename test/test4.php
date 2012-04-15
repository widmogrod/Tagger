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
         * @var \SplQueue
         */
        protected $wordlist;

        /**
         * @var \SplPriorityQueue
         */
        protected $wordPriority;


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
            if (empty($data)) {
                return array();
            }

            $data = $this->striptags($data);
            $data = $this->htmltrim($data);

            $words = preg_split('/[\s,]+/', $data);
            $words = array_filter($words, function($value){
                return preg_replace('/([^\pL\pN]+)/ui', null, $value);
            });

            $words = array_map(array($this, 'filterword'), $words);
            return $words;
        }
    }

    class Similarity
    {
        protected $minWordLength = 3;

        protected $minSimilarityPercent = 79;

        protected $similarity = array();

        public function __construct(array $words)
        {
            while ($word = array_pop($words))
            {
                if (mb_strlen($word) <= $this->minWordLength) {
                    continue;
                }

                foreach($words as $testWord)
                {
                    if ($word == $testWord) {
                        continue;
                    }
                    if (mb_strlen($testWord) <= $this->minWordLength) {
                        continue;
                    }

                    similar_text($word, $testWord, $percent);

                    if ($percent < $this->minSimilarityPercent) {
                        continue;
                    }

                    $this->setWordSimilarTo($word, $testWord);
                    $this->setWordSimilarTo($testWord, $word);
                }
            }
        }

        public function setWordSimilarTo($word, $similarWord)
        {
            if (!isset($this->similarity[$word])) {
                $this->similarity[$word] = array();
            }
            $this->similarity[$word][$similarWord] = true;
        }

        public function getSimilarWordsTo($word, $default = array())
        {
            return array_key_exists($word, $this->similarity)
                ? $this->similarity[$word]
                : $default;
        }

        public function toArray($flatWords = false)
        {
            if ($flatWords)
            {
                $result = array();
                foreach ($this->similarity as $word => $similarWords)
                {
                    $result[] = $word;
                    $result = array_merge($result, $similarWords);
                }
                return $result;
            }

            return $this->similarity;
        }

        public function setMinWordLength($minWordLength)
        {
            $this->minWordLength = $minWordLength;
        }

        public function getMinWordLength()
        {
            return $this->minWordLength;
        }

        public function setMinSimilarityPercent($minSimilarityPercent)
        {
            $this->minSimilarityPercent = $minSimilarityPercent;
        }

        public function getMinSimilarityPercent()
        {
            return $this->minSimilarityPercent;
        }
    }

    class Html
    {
        /**
         * @var \DOMDocument
         */
        protected $_document;

        /**
         * @var \DOMXPath
         */
        protected $_xpath;

        protected $_disableLibXmlErrors = true;

        public function retrieveTags($html)
        {
            $document = $this->getDocument();

            /*
             * If disable libxml errors is set to true then we see no more errors like that:
             * Warning: DOMDocument::loadHTML(): htmlParseEntityRef: expecting ';' in Entity
             */
            $previos = libxml_use_internal_errors($this->getDisableLibXmlErrors());
            $isLoaded = $document->loadHTML($html);
            libxml_use_internal_errors($previos);

            if (!$isLoaded)
            {
                $message = "Can't load html data from given source";
                throw new Exception\Exception($message);
            }

            $titleWords = array();
            $metaWords = array();
            $headerWords = array();
            $strongWords = array();
            $anchorWords = array();

            $text = new PlainText();

            $content = $this->getTextContentForPath('//html//title');
            $titleWords = $text->retrieveTags($content);

            // TODO install lower-case function & use it!
            // $content = $this->getTextContentForPath("//meta[contains(lower-case(@name), 'description')]", 'content');
            $content = $this->getTextContentForPath("//meta[contains(@name, 'description')]", 'content');
            $metaWords = $text->retrieveTags($content);

            $content = $this->getTextContentForPath("//meta[contains(@name, 'keywords')]", 'content');
            $metaWords = array_merge($metaWords, $text->retrieveTags($content));

            $content = $this->getTextContentForPath('//h1|//h2|//h3|//h4|//h5|//h6');
            $headerWords = $text->retrieveTags($content);

            $content = $this->getTextContentForPath('//strong|//b');
            $strongWords = $text->retrieveTags($content);

            $content = $this->getTextContentForPath('//a');
            $anchorWords = $text->retrieveTags($content);


            $result = array_merge($metaWords, $headerWords, $strongWords, $anchorWords);

            $result = array_filter($result, function($value){
                return mb_strlen($value) >= 3 && !is_numeric($value);
            });

            return $result;
        }

        private function getTextContentForPath($path, $attributeName = null)
        {
            $result = null;

            $xpath = $this->getXpath();
            $elements = $xpath->query($path);
            foreach($elements as $key => /* @var $element \DOMElement */ $element)
            {
                $result .= $attributeName ? $element->getAttribute($attributeName) : $element->textContent;
                $result .= ' ';
            }

            return $result;
        }

        public function getDocument()
        {
            if (null === $this->_document)
            {
                $this->_document = new \DOMDocument;
            }
            return $this->_document;
        }

        public function getXpath()
        {
            if (null === $this->_xpath)
            {
                $this->_xpath = new \DOMXPath($this->getDocument());
            }
            return $this->_xpath;
        }

        public function setDisableLibXmlErrors($flag)
        {
            $this->_disableLibXmlErrors = (bool) $flag;
        }

        public function getDisableLibXmlErrors()
        {
            return $this->_disableLibXmlErrors;
        }
    }

    class Priority
    {
        protected $_numberPriority;
        protected $_numberLangthPriority = array();

        protected $_stringPriority;
        protected $_stringLangthPriority = array();

        protected $_blackList = array();
        protected $_priorityList = array();

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

        public function addWordsToBlackList(array $words)
        {
            array_map(array($this, 'addWordToBlackList'), $words);
        }

        public function addWordToBlackList($word)
        {
            $this->_blackList[$word] = true;
        }

        public function addWordsToPriorityList(array $words, $priority)
        {
            while ($word = array_pop($words)) {
                $this->addWordToPriorityList($word, $priority);
            }
        }

        public function addWordToPriorityList($word, $priority)
        {
            $this->_priorityList[$word] = abs((int) $priority);
        }

        public function getPriority($value)
        {
            $value = (string) $value;
            $length = mb_strlen($value);

            if (isset($this->_blackList[$value])) {
                return 0;
            }
            if (isset($this->_priorityList[$value])) {
                return $this->_priorityList[$value];
            }

            return is_numeric($value)
                ? $this->getPriorityForNumber($length)
                : $this->getPriorityForString($length);
        }
    }

    /**
     * Test
     */

    $blackListWords = file(__DIR__ .'/_data/polskiespojniki.txt');
    $blackListWords = array_map('trim', $blackListWords);
    $blackListWords = array_filter($blackListWords);

    $html = file_get_contents(__DIR__ . '/_data/laksa.html');
    $html = file_get_contents(__DIR__ . '/_data/blog.widmogrod.html');
//    $html = file_get_contents(__DIR__ . '/_data/andredom.pl.html');

    $encoding = mb_detect_encoding($html);
    $oldEncoding = mb_internal_encoding();
    mb_internal_encoding($encoding);

    $html = html_entity_decode($html, null, $encoding);

    $priority = new Priority();
    $priority->setPriorityForNumber(5);
    $priority->setPriorityForString(20);
    $priority->setPriorityForStingLength(1, 0);
    $priority->setPriorityForStingLength(2, 2);
    $priority->setPriorityForStingLength(3, 4);
    $priority->setPriorityForStingLength(4, 8);
    $priority->setPriorityForStingLength(5, 16);

    $priority->addWordsToBlackList($blackListWords);


    $t = new Html();
    $words = $t->retrieveTags($html);
    $priority->addWordsToPriorityList($words, 40);
//var_dump($words);

    $words = array_count_values($words);
    $words = array_keys($words);
//var_dump($words);

    $s = new Similarity($words);
//    $similar = $s->toArray();
//var_dump($s->toArray(false));
//    $priority->addWordsToPriorityList($s->toArray(true), 80);


    $t = new PlainText();
    $summary = $t->retrieveTags($html);
    $summary = array_count_values($summary);
    asort($summary, SORT_STRING);

    $priorityQueue = new \SplPriorityQueue();
    foreach ($summary as $word => $occurrences)
    {
        $priorityNumber = 1;

        $similarWords = $s->getSimilarWordsTo($word);
//        $priorityNumber += count($similarWords);
        $priorityNumber *= $priority->getPriority($word);
//        $priorityNumber = pow($priorityNumber, $priority->getPriority($word));
        $priorityNumber += array_sum(array_map(array($priority,'getPriority'), $similarWords));
//        $priorityNumber += count($similarWords);
//        $priorityNumber = log($priorityNumber);

        $priorityQueue->insert($word, $priorityNumber);
    }

    foreach ($priorityQueue as $word) {
        var_dump($word);
    }
}