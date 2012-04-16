<?php
namespace Tagger\Utils;

/**
 * @author gabriel
 */
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
        return call_user_func($this->callback, $this->current());
    }
}
