<?php
/**
 * Abstract class for YBCore Model Event classes.
 */
abstract class YBCore_Model_Event_Abstract extends YBCore_Model_Abstract
{

    /**
     * participant list of the training
     *
     * @var array of YBCore_Model_User_Player|YBCore_Model_User_Coach
     */
    protected $_participantList = array();

    /**
     * day id of the training
     *
     * @var int
     */
    private $_day;

    /**
     * date of the training
     *
     * @var string
     */
    private $_date;

    /**
     * @todo refactor and remove this
     * instead of participant list set contracts employer and employee to appopriate properties
     * make 2 different classes for individual and team trainings
     * 
     * Sets the $_participantList.
     *
     * @param array $participantList
     */
    protected function _setParticipantList(array $participantList)
    {
        $this->_participantList = $participantList;
    }

    /**
     * @todo refactor and remove this
     * instead of participant list set contracts employer and employee to appopriate properties
     * make 2 different classes for individual and team trainings
     * 
     * Gets the $_participantList.
     *
     * @return array
     */
    public function getParticipantList()
    {
        return $this->_participantList;
    }

    /**
     * Sets the $_date.
     *
     * @param string $date
     */
    public function setDate($date)
    {
        $this->_date = (string) $date;
    }

    /**
     * Gets the $_date.
     *
     * @return string
     */
    public function getDate()
    {
        return $this->_date;
    }

    /**
     * Sets the $_day.
     *
     * @param int $day
     */
    public function setDay($day)
    {
        $this->_day = (int) $day;
    }

    /**
     * Gets the $_day.
     *
     * @return int
     */
    public function getDay()
    {
        return $this->_day;
    }

}