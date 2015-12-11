<?php
/**
 * @author Max Alzner
 * @license http://opensource.org/licenses/GPL-3.0
 */

/**
 * Contains methods and properties for querying an array.
 * 
 * @property array $stack Array of items.
 * 
 * @method array toArray()
 * @method bool all(string|callable|null $predicate)
 * @method bool any(string|callable|null $predicate)
 * @method int|float average(string|callable|null $selector)
 * @method bool contains(mixed $value)
 * @method ArrayQuery concat(ArrayQuery $other)
 * @method int count(string|callable|null $predicate)
 * @method ArrayQuery except(ArrayQuery $other)
 * @method mixed first()
 * @method ArrayQuery groupby(string|callable $selector)
 * @method ArrayQuery intersect(ArrayQuery $other)
 * @method ArrayQuery join(ArrayQuery $inner, string|callable $outerSelector, string|callable $innerSelector, string|callable $resultSelector)
 * @method mixed last()
 * @method int|float max(string|callable|null $selector)
 * @method int|float min(string|callable|null $selector)
 * @method ArrayQuery orderby(string|callable $selector)
 * @method ArrayQuery orderbydesc(string|callable $selector)
 * @method ArrayQuery reverse()
 * @method ArrayQuery select(string|callable $selector)
 * @method mixed single(string|callable|null $predicate)
 * @method ArrayQuery skip(int $count)
 * @method int|float sum(string|callable|null $selector)
 * @method ArrayQuery take(int $count)
 * @method ArrayQuery where(string|callable $predicate)
 * @method void each(string|callable $statement)
 */
class ArrayQuery
{
    public $stack = null;
    
    /**
     * Sets the given array to the $stack property.
     * 
     * @param array $stack Array of items.
     */
    function __construct(array $stack)
    {
        $this->stack = array_values($stack);
    }
    
    /**
     * @return string JSON encoded string of items in the instance.
     */
    function __toString()
    {
        return json_encode($this->stack);
    }
    
    /**
     * @return array Array of items in the instance.
     */
    function toArray()
    {
        return $this->stack;
    }
    
    /**
     * Determines whether all of the items in the instance match the predicate.
     * If no predicate is given items must be booleans.
     * 
     * @param string|callable|null $predicate Callback to test each item by.
     * 
     * @return bool Value indicating whether or not each item passed the predicate, or all are true.
     */
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
    
    /**
     * Determines if any of the items in the instance math the predicate.
     * If no predicate is given items must be booleans.
     * 
     * @param string|callable|null $predicate Callback to evaluate each item by.
     * 
     * @return bool Value indicating whether or any item passed the predicate, or any are true.
     */
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
    
    /**
     * Calculates the average value of all values selected.
     * If selector is not given items must be scalars.
     * 
     * @param string|callable|null $selector Callback to select from each item.
     * 
     * @return int|float The sum of all items.
     */
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
    
    /**
     * Searches the instance for the given value.
     * 
     * @param mixed $value A value to search for.
     * 
     * @return bool Value indicating whether or not the instance contains the given value.
     */
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
    
    /**
     * Concatenates this instance and the given instance together.
     * 
     * @param ArrayQuery $other An instance of the ArrayQuery class.
     * 
     * @return ArrayQuery New instacne with both instance concatenated together.
     */
    function concat(self $other)
    {
        return new self(array_merge($this->stack, $other->stack));
    }
    
    /**
     * Count the number of items in the instance, and optional limits the count by given the predicate.
     * 
     * @param string|callable|null $predicate Callback to evaluate each item by.
     * 
     * @return int The count of items.
     */
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
    
    /**
     * The difference between this instance and the given instance.
     * 
     * @param ArrayQuery $other An instance of the ArrayQuery class.
     * 
     * @return ArrayQuery New instance with the difference between both instances.
     */
    function except(self $other)
    {
        return new self(array_merge(array_diff($this->stack, $other->stack), array_diff($other->stack, $this->stack)));
    }
    
    /**
     * The first item in the instance.
     * 
     * @return mixed The first item.
     */
    function first()
    {
        return count($this->stack) > 0 ? $this->stack[0] : null;
    }
    
    /**
     * Groups by the value selected from each item in the instance.
     * 
     * @param string|callable $selector Callback to select from each item.
     * 
     * @return ArrayQuery New instance that has been sorted.
     */
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
    
    /**
     * The intersection between this instance and the given instance.
     * 
     * @param ArrayQuery $other An instance of the ArrayQuery class.
     * 
     * @return ArrayQuery New instance with the intersection between both instances.
     */
    function intersect(self $other)
    {
        return new self(array_intersect($this->stack, $other->stack));
    }
    
    /**
     * @return ArrayQuery New instance that has been joined.
     */
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
    
    /**
     * The last item in the instance.
     * 
     * @return mixed The last item.
     */
    function last()
    {
        $count = count($this->stack);
        return $count > 0 ? $this->stack[$count - 1] : null;
    }
    
    /**
     * Calculates the maxium value of all values selected.
     * If selector is not given items must be scalars.
     * 
     * @param string|callable|null $selector Callback to select from each item.
     * 
     * @return int|float The maxium of all selected values.
     */
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
    
    /**
     * Calculates the minium value of all values selected.
     * If selector is not given items must be scalars.
     * 
     * @param string|callable|null $selector Callback to select from each item.
     * 
     * @return int|float The minium of all selected values.
     */
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
    
    /**
     * Orders by the value selected from each item in the instance, ascending from least to greatest.
     * 
     * @param string|callable $selector Callback to select from each item.
     * 
     * @return ArrayQuery New instance that has been sorted.
     */
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
    
    /**
     * Orders by the value selected from each item in the instance, ascending from most to least.
     * 
     * @param string|callable $selector Callback to select from each item.
     * 
     * @return ArrayQuery New instance that has been sorted.
     */
    function orderbydesc($selector)
    {
        return new self(array_reverse($this->orderby($selector)->stack));
    }
    
    /**
     * Orders the items in the instance in reverse order.
     * 
     * @return ArrayQuery New instance that has been sorted.
     */
    function reverse()
    {
        return new self(array_reverse($this->stack));
    }
    
    /**
     * Selects a value from each item in the instance.
     * 
     * @param string|callable $selector Callback to select from each item.
     * 
     * @return ArrayQuery New instance that has been selected.
     */
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
    
    /**
     * Gets a single item from the instance.
     * 
     * @param string|callable|null $predicate Callback to evaluate each item by.
     * 
     * @return mixed A single value from the instance.
     */
    function single($predicate = null)
    {
        $query = $predicate !== null ? $this->where($predicate) : new self($this->stack);
        return count($query->stack) === 1 ? $query->stack[0] : null;
    }
    
    /**
     * Skips a number of elements.
     * 
     * @param int Number of elements to skip over.
     * 
     * @return ArrayQuery New instance after the specified count.
     */
    function skip($count)
    {
        return new self(array_slice($this->stack, intval($count)));
    }
    
    /**
     * Calculates the sum of values selected from each item in the instance.
     * If selector is not given items must be scalars.
     * 
     * @param string|callable|null $selector Callback to select from each item.
     * 
     * @return int|float The sum of all selected values.
     */
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
    
    /**
     * Takes a number of elements from the instance
     * 
     * @param int $count Number of elements to take.
     * 
     * @return ArrayQuery New instance after taking a number of items.
     */
    function take($count)
    {
        return new self(array_slice($this->stack, 0, intval($count)));
    }
    
    /**
     * Applies a condition to each item in the instance.
     * 
     * @param string|callable $predicate Callback to evaluate each item by.
     * 
     * @return ArrayQuery New instance where each item passes the condition.
     */
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
    
    /**
     * Calls a callback for each item in the instance.
     * 
     * @param string|callable $statement Callback function.
     */
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
    
    /**
     * Checks the validity of a specified statement.
     * 
     * @param string|callable $statement Callback function.
     * 
     * @return callable Usable callback function.
     */
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

/**
 * Creates an instance of the ArrayQuery class.
 * 
 * @return ArrayQuery An instance of the ArrayQuery class.
 */
function array_query($stack)
{
    return new ArrayQuery($stack);
}

?>