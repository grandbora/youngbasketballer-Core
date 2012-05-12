<?php
/**
 * Connection Day class
 * 
 * Each instance represents a connection day (the day on which event will happen) 
 */
class YBCore_Model_Connection_Day extends YBCore_Model_Abstract
{

    /**
     * id of the connection day
     *
     * @var int
     */
    private $_id;

    /**
     * id of the connection
     *
     * @var int
     */
    private $_connectionId;

    /**
     * day of the connection
     *
     * @var int
     */
    private $_dayId;

    /**
     * @param $id int[optional]
     */
    public function __construct($id = null)
    {
        if (null !== $id )
            $this->_load((int) $id);
    }

    /**
     * Sets the $_id.
     *
     * @param int $id
     */
    public function setId($id)
    {
        $this->_id = (int) $id;
    }

    /**
     * Gets the $_id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Sets the $_connectionId.
     *
     * @param int $connectionId
     */
    public function setConnectionId($connectionId)
    {
        $this->_connectionId = (int) $connectionId;
    }

    /**
     * Gets the $_connectionId.
     *
     * @return int
     */
    public function getConnectionId()
    {
        return $this->_connectionId;
    }

    /**
     * Sets the $_dayId.
     *
     * @param int $dayId
     */
    public function setDayId($dayId)
    {
        $this->_dayId = (int) $dayId;
    }

    /**
     * Gets the $_dayId.
     *
     * @return int
     */
    public function getDayId()
    {
        return $this->_dayId;
    }
    
    /**
     * Gets the name of the day.
     *
     * @return string
     */
    public function getDayName()
    {
        return YBCore_Utility_StringFormatter::getDayName($this->_dayId); 
    }
}