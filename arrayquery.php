<?php

class ArrayQuery
{
    protected $stack = null;
    
    function __construct(array $stack)
    {
        $this->stack = $stack;
    }
    
    function __toString()
    {
        return json_encode($this->stack, JSON_PRETTY_PRINT);
    }
}

?>