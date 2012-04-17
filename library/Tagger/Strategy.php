<?php
namespace Tagger;

/**
 * @author gabriel
 */
interface Strategy
{
    public function canExtractWords($value);
    public function extractWords($value);
    public function createWord($value);
}