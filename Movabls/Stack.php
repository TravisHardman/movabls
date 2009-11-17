<?php
/**
 * Maintains a Movabls stack for error reporting purposes
 * @author Travis Hardman
 */
class Movabls_Stack {

    private $stack = array();

    /**
     * Gets the full current stack
     * @return array
     */
    public function get() {

        return $this->stack;
        
    }

    /**
     * Gets the top item off the stack
     * @return array
     */
    public function top() {

        if (empty($this->stack))
            return array();
        else {
            $last = count($this->stack) - 1;
            return $this->stack[$last];
        }

    }

    /**
     * Pushes a level onto the stack, including the tagname to the previous level
     * @param array $level = array of information representing this level
     * @param string $tagname = name of the tag that spawned this level
     */
    public function push($info,$tagname = null) {

        if (!empty($tagname)) {
            $last = count($this->stack) - 1;
            $this->stack[$last]['tag'] = $tagname;
        }

        array_push($this->stack,$info);

    }

    /**
     * Pops the top level off the stack and removes the tagname from the previous level
     */
    public function pop() {

        array_pop($this->stack);

        $last = count($this->stack) - 1;
        if (isset($this->stack[$last]['tag']))
            unset($this->stack[$last]['tag']);

    }

}