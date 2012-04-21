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
    /* Set internal character encoding to UTF-8 */
    mb_internal_encoding("UTF-8");

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

    $priority = new \Tagger\Priority\Html();
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


    $document = new \Tagger\Document\Html($html);
    $document->setPriority($priority);
    $words = $document->getWordsList();

    foreach ($words as $key => /** @var $word Word */ $word)
    {
        echo str_pad($key, 3) ." - ". get_class($word) ." ". str_pad($word->getLength(), 4) ."". $word->getPrev() ." > ". $word ." > ". $word->getNext() ."\n";
    }
}