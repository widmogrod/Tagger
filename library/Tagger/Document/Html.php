<?php
namespace Tagger\Document;

use Tagger\Document,
    Tagger\Priority as Priority,
    Tagger\Strategy as Strategy,
    Tagger\Std as Std,
    Tagger\Exception\Exception;

/**
 * @author gabriel
 */
class Html implements Document
{
    protected $document;

    protected $wordList;

    protected $strategy;

    protected $priority;

    protected $disableLibXmlErrors = true;

    public function __construct($content)
    {
        $contentEncoding = mb_detect_encoding($content);
        $internalEncoding = mb_internal_encoding();

        $content = iconv($contentEncoding, $internalEncoding.'//TRANSLIT', $content);
        $content = html_entity_decode($content, null, $contentEncoding);

        $document = $this->getDocument();

        /*
        * If disable libxml errors is set to true then we see no more errors like that:
        * Warning: DOMDocument::loadHTML(): htmlParseEntityRef: expecting ';' in Entity
        */
        $previos = libxml_use_internal_errors($this->getDisableLibXmlErrors());
        $isLoaded = $document->loadHTML($content);
        libxml_use_internal_errors($previos);

        if (!$isLoaded)
        {
            $message = "Can't load html data from given source";
            throw new Exception($message);
        }
    }

    public function getWordsList()
    {
        if (null !== $this->wordList) {
            return $this->wordList;
        }

        $document = $this->getDocument();
        $iterator = new Std\DOMNodeListRecursiceIterator($document->childNodes);
        $iterator = new \RecursiveIteratorIterator($iterator);
        $iterator = new Std\CallbackFilterIterator($iterator, function(\DOMNode $node) {
            switch($node->nodeType)
            {
                case XML_COMMENT_NODE:
                case XML_CDATA_SECTION_NODE:
                    return false;

                default: return true;
            }
        });

        $iterator = new Html\Extractor($iterator, $this->getStrategy());
        return $this->wordList = new \RecursiveIteratorIterator($iterator);
    }

    protected function getDocument()
    {
        if (null === $this->document) {
            $this->document = new \DOMDocument();
        }
        return $this->document;
    }

    public function setDisableLibXmlErrors($flag)
    {
        $this->disableLibXmlErrors = (bool) $flag;
    }

    public function getDisableLibXmlErrors()
    {
        return $this->disableLibXmlErrors;
    }

    public function setStrategy(Strategy $strategy)
    {
        $this->strategy = $strategy;
    }

    public function getStrategy()
    {
        if (null === $this->strategy) {
            $this->strategy = new Strategy\Html($this->getPriority());
        }
        return $this->strategy;
    }

    public function setPriority(Priority $priority)
    {
        $this->priority = $priority;
    }

    public function getPriority()
    {
        if (null === $this->priority)
        {
            $this->priority = new Priority\Html();
        }
        return $this->priority;
    }
}