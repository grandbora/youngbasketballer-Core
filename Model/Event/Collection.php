<?php
/**
 * Collection class for YBCore Model Event classes.
 * 
 * @todo implement array accessible interface
 */
class YBCore_Model_Event_Collection extends YBCore_Collection_Abstract
{

    /**
     * Adds given events to the eventList.
     *
     * @param array of YBCore_Model_Event_Abstract
     */
    public function addEvents(array $eventListAdd)
    {
        $this->_setObjectList(array_merge($this->_getObjectList(), $eventListAdd));
        return $this;
    }

    /**
     * Clears the $_objectList.
     */
    public function clear()
    {
        $this->_setObjectList(array());
    }

    /**
     * Checks if the $_objectList is empty.
     *
     */
    public function isEmpty()
    {
        $objectList = $this->_getObjectList();
        return empty($objectList);
    }
}