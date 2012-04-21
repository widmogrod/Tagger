<?php
namespace Tagger\Priority;

use Tagger\Priority;

/**
 * @author gabriel
 */
class None implements Priority
{
    public function getPriority($value)
    {
        return 1;
    }
}