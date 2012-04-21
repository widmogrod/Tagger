<?php
namespace Tagger;

/**
 * @author gabriel
 */
interface Document
{
    public function __construct($content);
    public function getWordsList();
}