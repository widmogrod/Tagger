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

    class DOMDocumentRecursiceIterator implements  \RecursiveIterator, \Countable
    {
        /**
         * @var \DOMNodeList
         */
        protected $list;

        protected $count;

        protected $position;

        public function __construct(\DOMNodeList $list)
        {
            $this->list = $list;
            $this->count = $list->length;
            $this->position = 0;
        }

        /**
         * @return \DOMNode
         */
        public function current()
        {
            return $this->list->item($this->position);
        }

        /**
         * (PHP 5 &gt;= 5.1.0)<br/>
         * Return the key of the current element
         * @link http://php.net/manual/en/iterator.key.php
         * @return scalar scalar on success, integer
         * 0 on failure.
         */
        public function key()
        {
            return $this->position;
//            $current = $this->current();
//            return ($current instanceof \DOMText) ? $current->parentNode->tagName : $current->nodeName;
        }

        /**
         * (PHP 5 &gt;= 5.1.0)<br/>
         * Move forward to next element
         * @link http://php.net/manual/en/iterator.next.php
         * @return void Any returned value is ignored.
         */
        public function next()
        {
            ++$this->position;
        }

        /**
         * (PHP 5 &gt;= 5.1.0)<br/>
         * Rewind the Iterator to the first element
         * @link http://php.net/manual/en/iterator.rewind.php
         * @return void Any returned value is ignored.
         */
        public function rewind()
        {
            $this->position = 0;
//            $this->node = $this->first;
            //$this->node = $this->node->nextSibling->firstChild;
        }

        public function valid()
        {
            return $this->count > $this->position;
        }


        /**
         * (PHP 5 &gt;= 5.1.0)<br/>
         * Returns an iterator for the current entry.
         * @link http://php.net/manual/en/recursiveiterator.getchildren.php
         * @return RecursiveIterator An iterator for the current entry.
         */
        public function getChildren()
        {
            return new self($this->current()->childNodes);
        }

        /**
         * (PHP 5 &gt;= 5.1.0)<br/>
         * Returns if an iterator can be created fot the current entry.
         * @link http://php.net/manual/en/recursiveiterator.haschildren.php
         * @return bool true if the current entry can be iterated over, otherwise returns false.
         */
        public function hasChildren()
        {
            return $this->current()->hasChildNodes();
        }

        /**
         * (PHP 5 &gt;= 5.1.0)<br/>
         * Count elements of an object
         * @link http://php.net/manual/en/countable.count.php
         * @return int The custom count as an integer.
         * </p>
         * <p>
         * The return value is cast to an integer.
         */
        public function count()
        {
            return $this->count;
        }
    }

    class CallbackFilterIterator extends \FilterIterator
    {
        /**
         * @var \Closure
         */
        protected $callback;

        public function __construct(\Iterator $iterator, \Closure $callback)
        {
            parent::__construct($iterator);
            $this->callback = $callback;
        }

        public function accept()
        {
            return call_user_func($this->callback, $this->current(), $this->key());
        }
    }

    class WordsFromDOMNodeListRecursiveIterator implements \RecursiveIterator
    {
        /**
         * @var \Iterator
         */
        protected $iterator;

        /**
         * @var Strategy
         */
        protected $strategy;

        protected $cached = array();

        public function __construct(\Iterator $iterator, Strategy $strategy)
        {
            $this->iterator = $iterator;
            $this->strategy = $strategy;
        }

        /**
         * @return Word
         */
        public function current()
        {
            return isset($this->cached[$this->key()])
                ? $this->cached[$this->key()]
                : ($this->cached[$this->key()] = $this->strategy->createWord($this->iterator->current()));
        }

        public function key()
        {
            return $this->iterator->key();
        }

        public function next()
        {
            $this->iterator->next();
        }

        public function rewind()
        {
            $this->iterator->rewind();
        }

        public function valid()
        {
            return $this->iterator->valid();
        }

        public function getChildren()
        {
            $words = $this->strategy->extractWords($this->iterator->current());
            return new WordsFromDOMNodeRecursiveIterator($words);
        }

        public function hasChildren()
        {
            return $this->strategy->canExtractWords($this->iterator->current());
        }
    }

    interface Strategy
    {
        public function canExtractWords($value);
        public function extractWords($value);
        public function createWord($value);
    }

    class HtmlStrategy implements Strategy
    {
        const WORD_DELIMETER = '/[\s,]+/';
//        const WORD_DELIMETER = ' ';

        protected $priority;

        public function __construct(HtmlPriority $priority)
        {
            $this->priority = $priority;
        }

        public function canExtractWords($value)
        {
            if (!$value instanceof \DOMNode) {
                return false;
            }

            /** @var $value \DOMNode */
            $phrase = $value->nodeValue;
            $phrase = trim($phrase);
            if (empty($phrase)) {
                return false;
            }

            return (false !== preg_match(self::WORD_DELIMETER, $phrase));
//            return (false !== strpos($phrase, self::WORD_DELIMETER));
        }

        public function extractWords($value)
        {
            $phrase = $value->nodeValue;
            $phrase = trim($phrase);

            $words = preg_split(self::WORD_DELIMETER, $phrase);
//            $words = explode(self::WORD_DELIMETER, $phrase);

            $words = array_filter($words, function($value){
                return preg_replace('/([^\pL\pN]+)/ui', null, $value);
            });
            $words = array_map(array($this, 'filterword'), $words);

            $basePriority = $this->priority->getPriority($value);

            $result = array();

//            $prev = null;
            foreach ($words as $word)
            {
                $word = $this->createWord($word);
                $word->setPriority($this->priority->getPriority($word) + $basePriority);

//                if (null !== $prev) {
//                    $word->setPrev($prev);
//                }
//                $prev = $word;

                $result[] = $word;
            }
            return $result;
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

        public function createWord($value)
        {
            if ($value instanceof \DOMNode) {
                $value = $value->nodeValue;
            }

            return new Word($value);
        }
    }

    class WordsFromDOMNodeRecursiveIterator implements \RecursiveIterator, \Countable
    {
        protected $data = array();

        protected $count = 0;

        protected $position = 0;

        public function __construct(array $data)
        {
            $this->data = array_values($data);
            $this->count = count($data);
        }

        public function current()
        {
            return $this->data[$this->position];
        }

        public function key()
        {
            return $this->position;
        }

        public function next()
        {
            ++$this->position;
        }

        public function rewind()
        {
            $this->position = 0;
        }

        public function valid()
        {
            return $this->position < $this->count;
        }

        public function getChildren()
        {
            return false;
        }

        public function hasChildren()
        {
            return false;
        }

        public function count()
        {
            return $this->count;
        }
    }

    class Word
    {
        protected $prev;

        protected $next;

        protected $word;

        protected $length;

        protected $priority;

        public function __construct($word)
        {
            $this->word = $word;
            $this->length = mb_strlen($word);
        }

        public function __toString()
        {
            return (string) $this->word;
        }

        public function setPriority($priority)
        {
            $this->priority = $priority;
        }

        public function getPriority()
        {
            return $this->priority;
        }

        public function setNext(Word $next, $setPrev = true)
        {
            $this->next = $next;

            if ($setPrev) {
                $next->setPrev($this, false);
            }
        }

        public function getNext()
        {
            return $this->next;
        }

        public function setPrev(Word $prev, $setNext = true)
        {
            $this->prev = $prev;

            if ($setNext) {
                $prev->setNext($this, false);
            }
        }

        public function getPrev()
        {
            return $this->prev;
        }

        public function getLength()
        {
            return $this->length;
        }
    }

    class WordGroup
    {
        protected $words = array();

        protected $priority = 0;

        public function getWords()
        {
            return $this->words;
        }

        public function __toString()
        {
            return (string) implode(', ', array_map('strval', $this->words));
        }

        public function getNext()
        {}

        public function getLength()
        {
            return $this->words[0]->getLength();
        }

        public function addWord(Word $word)
        {
            $this->words[] = $word;
            $this->priority += $word->getPriority();
        }

        public function getPriority()
        {
            return $this->priority;
        }
    }

    class GroupingIterator implements \Iterator
    {
        protected $iterator;

        public function __construct(\Iterator $iterator)
        {
            $a = new \ArrayObject(array());
            foreach ($iterator as $word)
            {
                $key = (string) $word;
                if (!$a->offsetExists($key)) {
                    $a->offsetSet($key, new WordGroup());
                }
                $a->offsetGet($key)->addWord($word);
            }
            $this->iterator = new \ArrayIterator($a);
        }

        /**
         * @return Word
         */
        public function current()
        {
            return $this->iterator->current();
        }

        public function key()
        {
            return $this->iterator->key();
        }

        public function next()
        {
            $this->iterator->next();
        }

        public function rewind()
        {
            $this->iterator->rewind();
        }

        public function valid()
        {
            return $this->iterator->valid();
        }
    }

    class ClonableIterator implements \Iterator, \Countable, \SeekableIterator
    {
        const CACHE_KEY = 0;
        const CACHE_VALUE = 1;

        protected $cache = array();
        protected $count = 0;
        protected $position = 0;

        public function __construct(\Iterator $iterator)
        {
            foreach ($iterator as $key => $value)
            {
                ++$this->count;
                $this->cache[] = array(
                    self::CACHE_KEY => $key,
                    self::CACHE_VALUE => $value
                );
            }
        }

        public function current()
        {
            return $this->cache[$this->position][self::CACHE_VALUE];
        }

        public function next()
        {
            return ++$this->position;
        }

        public function key()
        {
            return $this->position;
        }

        public function valid()
        {
            return $this->count > $this->position;
        }

        public function rewind()
        {
            $this->position = 0;
        }

        public function count()
        {
            return $this->count;
        }

        public function seek($position)
        {
            if (!isset($this->cache[$position])) {
                throw new \OutOfBoundsException("invalid seek position ($position)");
            }
            $this->position = $position;
        }

        public function __clone()
        {
            //
        }
    }

    class SimilarityIterator implements \Iterator, \Countable
    {
        protected $minWordLength = 3;

        protected $minSimilarityPercent = 79;

        protected $similarity = array();

//        public function __construct(array $words)
//        {
//            while ($word = array_pop($words))
//            {
//                if (mb_strlen($word) <= $this->minWordLength) {
//                    continue;
//                }
//
//                foreach($words as $testWord)
//                {
//                    if ($word == $testWord) {
//                        continue;
//                    }
//                    if (mb_strlen($testWord) <= $this->minWordLength) {
//                        continue;
//                    }
//
//                    similar_text($word, $testWord, $percent);
//
//                    if ($percent < $this->minSimilarityPercent) {
//                        continue;
//                    }
//
//                    $this->setWordSimilarTo($word, $testWord);
//                    $this->setWordSimilarTo($testWord, $word);
//                }
//            }
//        }

        protected $iterator;

        public function __construct(\Iterator $iterator)
        {
//            ini_set("zend.ze1_compatibility_mode", 0);
            $a = new \ArrayObject(array());

            $iterator = new ClonableIterator($iterator);
            foreach ($iterator as $position => /** @var $word Word */ $word)
            {
                $key = (string) $word;
                $length = $word->getLength();

//                echo "$key \n";

                if ($length <= $this->minWordLength) {
                    continue;
                }

                if (!$a->offsetExists($key)) {
                    $a->offsetSet($key, new WordGroup());
                }

                /** @var $group WordGroup */
                $group = $a->offsetGet($key);
                $group->addWord($word);

                $it = new CallbackFilterIterator(clone $iterator, function(Word $word) use ($length, $key) {
                    $l = $word->getLength();
                    if ($l < 3) {
                        return false;
                    }

                    $minDiff = 2;
                    $diff = $l < $length ? $l : $length;
                    $diff -= $minDiff;

                    if ($diff < $minDiff) {
                        return false;
                    }
                    else
                    {
                        $base = mb_substr($word, 0, $diff);
                        $to = mb_substr($key, 0, $diff);
                    }

//                    var_dump(array("$base :: $to", "$word :: $key"));
                    return $base == $to;
                });

                foreach($it as /** @var $testWord Word */ $testWord)
                {
                    if ((string) $word == (string) $testWord) {
                        $group->addWord($testWord);
                        continue;
                    }

                    similar_text((string) $word, (string) $testWord, $percent);

                    if ($percent < $this->minSimilarityPercent) {
                        continue;
                    }

                    $group->addWord($testWord);
                }

//                 var_dump((string) $group);
            }
            $this->iterator = new \ArrayIterator($a);
//            var_dump($this->iterator->count());
        }

        /**
         * @return Word
         */
        public function current()
        {
            return $this->iterator->current();
        }

        public function key()
        {
            return $this->iterator->key();
        }

        public function next()
        {
            $this->iterator->next();
        }

        public function rewind()
        {
            $this->iterator->rewind();
        }

        public function valid()
        {
            return $this->iterator->valid();
        }

        public function count()
        {
            return $this->iterator->count();
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

        /**
         * @var HtmlPriority
         */
        protected $_priority;

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

            $text = new PlainText();

            $it = new DOMDocumentRecursiceIterator($document->childNodes);
            $ir = new \RecursiveIteratorIterator($it);

            $ir = new CallbackFilterIterator($ir, function(\DOMNode $node) {
                switch($node->nodeType)
                {
                    case XML_COMMENT_NODE:
                    case XML_CDATA_SECTION_NODE:
                        return false;

                    default: return true;
                }
            });

            //
            $strategy = new HtmlStrategy($this->getPriority());
            $ir = new WordsFromDOMNodeListRecursiveIterator($ir, $strategy);
            $ir = new \RecursiveIteratorIterator($ir);

            $ir = new CallbackFilterIterator($ir, function($value){
                return preg_replace('/([^\pL\pN]+)/ui', null, $value);
            });

//            $ir = new GroupingIterator($ir);
            $ir = new SimilarityIterator($ir);
            $ir->rewind();
//            var_dump($ir->key(), count($ir->count()));
//echo __LINE__ . "\n";
            $priorityQueue = new \SplPriorityQueue();
            foreach ($ir as /** @var $word Word */ $word) {
//                echo __LINE__ . "\n";
//                echo $word. "\n";
                $priorityQueue->insert($word, $word->getPriority());
            }
//            echo __LINE__ . "\n";

//            $priorityQueue->top();
            foreach ($priorityQueue as $word) {
                echo $word .' '. $word->getNext() ." \n";
            }

            die;

            $priority = $this->getPriority();

            $elements = $document->getElementsByTagName('*');
            foreach ($elements as /* @var $element \DOMElement */ $element)
            {
                $tagName = trim($element->tagName);
                var_dump($element->nodeName);
                switch ($tagName)
                {
//                    case 'ul':
                    case 'br':
                    case 'hr':
                    case 'table':
                    case 'html':
                    case 'head':
                    case 'meta':
                    case 'body':
                    case 'link':
                    case 'style':
                    case 'script':
                        break;

                    default:

                        $content = null;
//                        switch($tagName)
//                        {
//                            case 'img': $content = $element->getAttribute('alt'); break;
//                        }

                        if (!$content && !$element->hasChildNodes()) {
                            continue 2;
                        }

                        if (!$content) {
                            $content = $element->nodeValue;
                        }

                        echo $content . " \n";

                        $words = $text->retrieveTags($content);
                        $priority->addWordsForTag($this->filter($words), $tagName);
                }
            }

            // TODO install lower-case function & use it!
            // $content = $this->getTextContentForPath("//meta[contains(lower-case(@name), 'description')]", 'content');
            $content = $this->getTextContentForPath("//meta[contains(@name, 'description')]", 'content');
            $words = $text->retrieveTags($content);
            $priority->addWordsForTag($words, 'meta[name="description"]');

            $content = $this->getTextContentForPath("//meta[contains(@name, 'keywords')]", 'content');
            $words = $text->retrieveTags($content);
            $priority->addWordsForTag($words, 'meta[name="keywords"]');
        }

        private function filter(array $result)
        {
            return array_filter($result, function($value){
                return mb_strlen($value) >= 3 && !is_numeric($value);
            });
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

        public function getPriority()
        {
//            if (null === $this->_priority) {
//                $this->_priority = new HtmlPriority();
//            }
            return $this->_priority;
        }

        /**
         * @param HtmlPriority $priority
         */
        public function setPriority(HtmlPriority $priority)
        {
            $this->_priority = $priority;
        }
    }

    interface Priority
    {
        public function getPriority($word);
    }

    class SimplePriority implements Priority
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

    class HtmlPriority implements Priority
    {
        const WORD_TOTAL_PRIORITY = '__all__';
        const DEFAULT_PRIORITY = 1;

        protected $_wordPriority = array();

        protected $_tagNamePriority = array();

        protected $_blackListWords = array();

        protected $_numberPriority;
        protected $_numberLangthPriority = array();

        protected $_stringPriority;
        protected $_stringLangthPriority = array();

        public function getPriority($word)
        {
            /** @var $word \DOMNode */
            if ($word instanceof \DOMNode) {
                return $this->getTagNamePriority($word->parentNode->tagName);
            }

            $word = mb_strtolower($word);
            if (isset($this->_blackListWords[$word])) {
                return 0;
            }

            if (isset($this->_wordPriority[$word])) {
                return $this->_wordPriority[$word][self::WORD_TOTAL_PRIORITY];
            }

            $length = mb_strlen($word);

            return is_numeric($word)
                ? $this->getPriorityForNumber($length)
                : $this->getPriorityForString($length);
        }

        public function setTagNamePriority($tagName, $priority)
        {
            $tagName = strtolower($tagName);
            $this->_tagNamePriority[$tagName] = (int) $priority;
        }

        public function getTagNamePriority($tagName)
        {
            $tagName = strtolower($tagName);
            return array_key_exists($tagName, $this->_tagNamePriority)
                ? $this->_tagNamePriority[$tagName]
                : self::DEFAULT_PRIORITY;
        }

        public function addWordsForTag(array $words, $tagName)
        {
            while ($word = array_pop($words)) {
                $this->addWordForTag($word, $tagName);
            }
        }

        public function addWordForTag($word, $tagName)
        {
            $word = mb_strtolower($word);
            if (!array_key_exists($word, $this->_wordPriority)) {
                $this->_wordPriority[$word] = array(
                    self::WORD_TOTAL_PRIORITY => self::DEFAULT_PRIORITY
                );
            }

            $priority = $this->getTagNamePriority($tagName);
            $this->_wordPriority[$word][$tagName] = $priority;
            $this->_wordPriority[$word][self::WORD_TOTAL_PRIORITY] += $priority;
        }

        public function addWordsToBlackList(array $words)
        {
            array_map(array($this, 'addWordToBlackList'), $words);
        }

        public function addWordToBlackList($word)
        {
            $this->_blackListWords[$word] = true;
        }

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

//        public function __destruct()
//        {
//            var_dump($this->_wordPriority);
//        }
    }

    /**
     * Test
     */

    $blackListWords = file(__DIR__ .'/_data/polskiespojniki.txt');
    $blackListWords = array_map('trim', $blackListWords);
    $blackListWords = array_filter($blackListWords);

    $html = file_get_contents(__DIR__ . '/_data/laksa.html');
//    $html = FILE_GET_CONTENTS(__DIR__ . '/_data/blog.widmogrod.html');
//    $html = file_get_contents(__DIR__ . '/_data/andredom.pl.html');
//    $html = file_get_contents(__dir__ . '/_data/matejko.html');
//    $html = file_get_contents(__DIR__ . '/_data/mostowy.com.pl.html');
//    $html = file_get_contents(__DIR__ . '/_data/php.net.preg.match.html');



    $encoding = mb_detect_encoding($html);
    $oldEncoding = mb_internal_encoding();
    mb_internal_encoding($encoding);

    $html = html_entity_decode($html, null, $encoding);

//    $priority = new SimplePriority();
//    $priority->setPriorityForNumber(5);
//    $priority->setPriorityForString(20);
//    $priority->setPriorityForStingLength(1, 0);
//    $priority->setPriorityForStingLength(2, 2);
//    $priority->setPriorityForStingLength(3, 4);
//    $priority->setPriorityForStingLength(4, 8);
//    $priority->setPriorityForStingLength(5, 16);
//
//    $priority->addWordsToBlackList($blackListWords);


    $priority = new HtmlPriority();
    $priority->addWordsToBlackList($blackListWords);
    $priority->setPriorityForNumber(5);
    $priority->setPriorityForString(20);
    $priority->setPriorityForStingLength(1, 0);
    $priority->setPriorityForStingLength(2, 2);

    $priority->setTagNamePriority('a', 25);
    $priority->setTagNamePriority('b', 27);
    $priority->setTagNamePriority('strong', 27);
    $priority->setTagNamePriority('h6', 27);
    $priority->setTagNamePriority('h5', 29);
    $priority->setTagNamePriority('h4', 31);
    $priority->setTagNamePriority('h3', 35);
    $priority->setTagNamePriority('h2', 41);
    $priority->setTagNamePriority('h1', 70);
    $priority->setTagNamePriority('title', 120);
    $priority->setTagNamePriority('meta[name="description"]', 120);
    $priority->setTagNamePriority('meta[name="keywords"]', 120);

    $t = new Html();
    $t->setPriority($priority);
    $words = $t->retrieveTags($html);

//    $priority->addWordsToPriorityList($words, 40);
//var_dump($words);

//    $words = array_count_values($words);
//    $words = array_keys($words);
//var_dump($words);

//    $similar = $s->toArray();
//var_dump($s->toArray(false));
//    $priority->addWordsToPriorityList($s->toArray(true), 80);


    $t = new PlainText();
    $words = $t->retrieveTags($html);

    $summary = $words;
    $summary = array_count_values($summary);
    asort($summary, SORT_STRING);

    $s = new Similarity($words);
//    $summary = $s->toArray();

    $priorityQueue = new \SplPriorityQueue();
    foreach ($summary as $word => $occurrences)
    {
        $priorityNumber = 1;

        $priorityNumber *= $priority->getPriority($word);
        if ($priorityNumber == 0) {
            goto insert;
        }

        $similarWords = $s->getSimilarWordsTo($word);
        $priorityNumber += array_reduce(array_map(array($priority,'getPriority'), $similarWords), function($a, $b) {
            return $a * $b;
        });

        insert:
        $priorityQueue->insert($word, $priorityNumber);
    }

//    $priorityQueue = new \IteratorIterator($priorityQueue);
//    $priorityQueue = new \LimitIterator($priorityQueue, 20);

    foreach ($priorityQueue as $word) {
        var_dump($word);
    }
}