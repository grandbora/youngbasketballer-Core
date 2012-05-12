<?php
/**
 * Abstract class for building for Collection classes.
 */
abstract class YBCore_Collection_Abstract extends YBCore_Abstract implements arrayaccess, Iterator, Countable
{

    /**
     * Array of loaded objects.
     *
     * @var array
     */
    private $_objectList = array();

    /**
     * Sets the $_objectList.
     *
     * @param array of YBCore_Abstract
     */
    protected function _setObjectList(array $eventList)
    {
        $this->_objectList = $eventList;
    }

    /**
     * Gets the $_objectList.
     *
     * @return array of YBCore_Abstract
     */
    protected function _getObjectList()
    {
        return $this->_objectList;
    }

    // methods belove should not be changed/touched unless you are me :) 
    /**
     * arrayaccess methods
     */
    public function offsetSet($offset, $value)
    {
        if (null === $offset)
            $this->_objectList[] = $value;
        else
            $this->_objectList[$offset] = $value;
    }

    public function offsetExists($offset)
    {
        return isset($this->_objectList[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->_objectList[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->_objectList[$offset]) ? $this->_objectList[$offset] : null;
    }

    /**
     * iterator methods
     */
    public function rewind()
    {
        reset($this->_objectList);
    }

    public function current()
    {
        return current($this->_objectList);
    }

    public function key()
    {
        return key($this->_objectList);
    }

    public function next()
    {
        return next($this->_objectList);
    }

    public function valid()
    {
        return $this->current() !== false;
    }

    /**
     * countable methods
     */
    public function count()
    {
        return count($this->_objectList);
    }
}