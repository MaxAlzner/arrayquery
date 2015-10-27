<?php

class ArrayQuery
{
    public $stack = null;
    
    function __construct(array $stack)
    {
        $this->stack = array_values($stack);
    }
    
    function __toString()
    {
        return json_encode($this->stack);
    }
    
    function toArray()
    {
        return $this->stack;
    }
    
    function all($predicate = null)
    {
        $query = true;
        $callback = $this->validate($predicate);
        foreach($this->stack as $item)
        {
            if ($callback !== false)
            {
                $query = $query && boolval(call_user_func($callback, $item));
            }
            else if (is_bool($item))
            {
                $query = $query && $item;
            }
        }
        
        return $query;
    }
    
    function any($predicate = null)
    {
        $callback = $this->validate($predicate);
        foreach($this->stack as $item)
        {
            if ($callback !== false && boolval(call_user_func($callback, $item)))
            {
                return true;
            }
            else if (is_bool($item) && $item === true)
            {
                return true;
            }
        }
        
        return false;
    }
    
    function average($selector = null)
    {
        $total = 0;
        $count = 0;
        $callback = $this->validate($selector);
        foreach($this->stack as $item)
        {
            $query = $callback !== false ? call_user_func($callback, $item) : $item;
            if (is_int($query) || is_float($query))
            {
                $total += $query;
                $count++;
            }
        }
        
        return $count !== 0 ? ($total / $count) : 0;
    }
    
    function contains($value)
    {
        foreach($this->stack as $item)
        {
            if ($item === $value)
            {
                return true;
            }
        }
        
        return false;
    }
    
    function concat(self $other)
    {
        return new self(array_merge($this->stack, $other->stack));
    }
    
    function count($predicate = null)
    {
        $count = 0;
        $callback = $this->validate($predicate);
        foreach($this->stack as $item)
        {
            $count += $callback !== false ? (call_user_func($callback, $item) !== true ? 0 : 1) : 1;
        }
        
        return $count;
    }
    
    function except(self $other)
    {
        return new self(array_merge(array_diff($this->stack, $other->stack), array_diff($other->stack, $this->stack)));
    }
    
    function first()
    {
        return count($this->stack) > 0 ? $this->stack[0] : null;
    }
    
    function groupby($selector)
    {
        $query = new self([]);
        $callback = $this->validate($selector);
        if ($callback !== false)
        {
            $groups = [];
            foreach ($this->stack as $item)
            {
                $key = call_user_func($callback, $item);
                $key = is_scalar($key) ? $key : 0;
                $groups[$key][] = $item;
            }
            
            ksort($groups);
            foreach ($groups as $group)
            {
                foreach ($group as $item)
                {
                    $query->stack[] = $item;
                }
            }
        }
        
        return $query;
    }
    
    function intersect(self $other)
    {
        return new self(array_intersect($this->stack, $other->stack));
    }
    
    function join(self $inner, $outerSelector, $innerSelector, $resultSelector)
    {
        $query = new self([]);
        $outerCallback = $this->validate($outerSelector);
        $innerCallback = $this->validate($innerSelector);
        $resultCallback = $this->validate($resultSelector);
        if ($resultCallback !== false)
        {
            foreach ($this->stack as $outerItem)
            {
                $outerKey = $outerCallback !== false ? call_user_func($outerCallback, $outerItem) : null;
                foreach ($inner->stack as $innerItem)
                {
                    $innerKey = $innerCallback !== false ? call_user_func($innerCallback, $innerItem) : null;
                    if ($outerKey !== null && $innerKey !== null && $outerKey === $innerKey)
                    {
                        $result = call_user_func($resultCallback, $outerItem, $innerItem);
                        if (isset($result))
                        {
                            $query->stack[] = $result;
                        }
                    }
                }
            }
        }
        
        return $query;
    }
    
    function last()
    {
        $count = count($this->stack);
        return $count > 0 ? $this->stack[$count - 1] : null;
    }
    
    function max($selector = null)
    {
        $max = null;
        $callback = $this->validate($selector);
        foreach ($this->stack as $item)
        {
            $query = $callback !== false ? call_user_func($callback, $item) : $item;
            if (is_scalar($query))
            {
                $max = $max === null || $max < $query ? $query : $max;
            }
        }
        
        return $max;
    }
    
    function min($selector = null)
    {
        $min = null;
        $callback = $this->validate($selector);
        foreach ($this->stack as $item)
        {
            $query = $callback !== false ? call_user_func($callback, $item) : $item;
            if (is_scalar($query))
            {
                $min = $min === null || $min > $query ? $query : $min;
            }
        }
        
        return $min;
    }
    
    function orderby($selector)
    {
        $query = new self([]);
        $callback = $this->validate($selector);
        if ($callback !== false)
        {
            $ordered = [];
            foreach($this->stack as $item)
            {
                $key = call_user_func($callback, $item);
                if (isset($key))
                {
                    $ordered[$key] = $item;
                }
            }
            
            foreach ($ordered as $item)
            {
                $query->stack[] = $item;
            }
        }
        
        return $query;
    }
    
    function orderbydesc($selector)
    {
        return new self(array_reverse($this->orderby($selector)->stack));
    }
    
    function reverse()
    {
        return new self(array_reverse($this->stack));
    }
    
    function select($selector)
    {
        $query = new self([]);
        $callback = $this->validate($selector);
        if ($callback !== false)
        {
            foreach($this->stack as $item)
            {
                $result = call_user_func($callback, $item);
                if (isset($result))
                {
                    $query->stack[] = $result;
                }
            }
        }
        
        return $query;
    }
    
    function single($predicate = null)
    {
        $query = $predicate !== null ? $this->where($predicate) : new self($this->stack);
        return count($query->stack) === 1 ? $query->stack[0] : null;
    }
    
    function skip($count, $predicate = null)
    {
        $query = $predicate !== null ? $this->where($predicate) : new self($this->stack);
        return new self(array_slice($query->stack, intval($count)));
    }
    
    function sum($selector = null)
    {
        $sum = 0;
        $callback = $this->validate($selector);
        foreach ($this->stack as $item)
        {
            $result = $callback !== false ? call_user_func($callback, $item) : $item;
            if (is_int($result) || is_float($result))
            {
                $sum += $result;
            }
        }
        
        return $sum;
    }
    
    function take($start, $predicate = null)
    {
        $query = $predicate !== null ? $this->where($predicate) : new self($this->stack);
        return new self(array_slice($query->stack, 0, intval($start)));
    }
    
    function where($predicate)
    {
        $query = new self([]);
        $callback = $this->validate($predicate);
        if ($callback !== false)
        {
            foreach($this->stack as $item)
            {
                if (call_user_func($callback, $item) === true)
                {
                    $query->stack[] = $item;
                }
            }
        }
        
        return $query;
    }
    
    function each($statement)
    {
        $callback = $this->validate($statement);
        if ($callback !== false)
        {
            foreach($this->stack as $item)
            {
                call_user_func($callback, $item);
            }
        }
    }
    
    protected function validate($statement)
    {
        if (is_callable($statement))
        {
            return $statement;
        }
        else if (is_string($statement))
        {
            $segments = explode('=>', $statement);
            if (count($segments) > 1)
            {
                $arg = trim(str_replace(['(', ')'], '', $segments[0]));
                $body = trim(implode('=>', array_splice($segments, 1)));
                try
                {
                    eval('$callback = function (' . $arg . ') {return ' . $body . ';};');
                    return $callback;
                }
                catch (Exception $e)
                {
                    throw new Exception('Statement is invalid: ' . $statement, 0, $e);
                }
            }
        }
        
        return false;
    }
}

function array_query($stack)
{
    return new ArrayQuery($stack);
}

?>