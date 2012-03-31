<?php
/**
 * @author gabriel
 */
 
namespace Tagger;

interface Strategy
{
    public function retrieveTags($data);
}