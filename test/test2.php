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
    /**
     * Test
     */

//    $html = file_get_contents(__DIR__ . '/_data/laksa.html');
    $html = file_get_contents(__DIR__ . '/_data/andredom.pl.html');

    $encoding = 'utf-8';
    mb_internal_encoding($encoding);

    $html = html_entity_decode($html, null, $encoding);

    $options = new Options();
    $options->setPriorityForNumber(10);
    $options->setPriorityForString(35);
    $options->setPriorityForStingLength(1, 1);
    $options->setPriorityForStingLength(2, 2);
    $options->setPriorityForStingLength(3, 3);
    $options->setPriorityForStingLength(4, 8);
    $options->setPriorityForStingLength(5, 15);

    $strategy = new Strategy\PlainText($options);
    $tags = $strategy->retrieveTags($html);
    var_dump($tags);
}