<?php
/**
 * Class for YBCore Model Game Abstract.
 */
class YBCore_Model_Event_Game_Abstract extends YBCore_Model_Event_Abstract
{

    /**
     * id of the user
     *
     * @var int
     */
    private $_id;

    /**
     * id of the homeTeam
     *
     * @var int
     */
    private $_homeTeamId;

    /**
     * homeTeam
     *
     * @var YBCore_Model_Team
     */
    private $_homeTeam;

    /**
     * id of the awayTeam
     *
     * @var int
     */
    private $_awayTeamId;

    /**
     * awayTeam
     *
     * @var YBCore_Model_Team
     */
    private $_awayTeam;

    /**
     * status of the game event  
     *
     * @var int YBCore_Model_Event_Game_Mapper_*::STATUS
     */
    private $_status;

    /**
     * @param [optional] int $id = null 
     */
    public function __construct($id = null)
    {
        $status = $this->_getMapperConstant('STATUS');
        $this->setStatus($status);
        
        if (null !== $id)
            $this->_load((int) $id);
    }

    /**
     * Sets the $_id.
     *
     * @param $id int
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
     * Gets the $_participantList.
     * @todo PERF home and away teams loaded separetly, can be merged to save request
     * 
     * @return array
     */
    public function getParticipantList()
    {
        if (null === $this->_participantList)
        {
            $participantList = array($this->getHomeTeam(), $this->getAwayTeam());
            $this->_setParticipantList($participantList);
        }
        return $this->_participantList;
    }

    /**
     * Sets the $_homeTeamId.
     *
     * @param int $homeTeamId
     */
    public function setHomeTeamId($homeTeamId)
    {
        $this->_clearHomeTeam();
        $this->_homeTeamId = (int) $homeTeamId;
    }

    /**
     * Gets the $_homeTeamId.
     *
     * @return int
     */
    public function getHomeTeamId()
    {
        return $this->_homeTeamId;
    }

    /**
     * Sets the $_homeTeam.
     *
     * @param YBCore_Model_Team $homeTeam
     */
    public function _setHomeTeam(YBCore_Model_Team $homeTeam)
    {
        $this->_homeTeam = $homeTeam;
    }

    /**
     * Gets the $_homeTeam.
     *
     * @return YBCore_Model_Team
     */
    public function getHomeTeam()
    {
        if (null === $this->_homeTeam)
        {
            $homeTeamId = $this->getHomeTeamId();
            if (null !== $homeTeamId)
                $this->_setHomeTeam(new YBCore_Model_Team($homeTeamId));
        }
        return $this->_homeTeam;
    }

    /**
     * Clears the $_homeTeam.
     *
     */
    public function _clearHomeTeam()
    {
        $this->_homeTeam = null;
    }

    /**
     * Sets the $_awayTeamId.
     *
     * @param int $awayTeamId
     */
    public function setAwayTeamId($awayTeamId)
    {
        $this->_clearAwayTeam();
        $this->_awayTeamId = (int) $awayTeamId;
    }

    /**
     * Gets the $_awayTeamId.
     *
     * @return int
     */
    public function getAwayTeamId()
    {
        return $this->_awayTeamId;
    }

    /**
     * Sets the $_awayTeam.
     *
     * @param YBCore_Model_Team $awayTeam
     */
    public function _setAwayTeam(YBCore_Model_Team $awayTeam)
    {
        $this->_awayTeam = $awayTeam;
    }

    /**
     * Gets the $_awayTeam.
     *
     * @return YBCore_Model_Team
     */
    public function getAwayTeam()
    {
        if (null === $this->_awayTeam)
        {
            $awayTeamId = $this->getAwayTeamId();
            if (null !== $awayTeamId)
                $this->_setAwayTeam(new YBCore_Model_Team($awayTeamId));
        }
        return $this->_awayTeam;
    }

    /**
     * Clears the $_awayTeam.
     *
     */
    public function _clearAwayTeam()
    {
        $this->_awayTeam = null;
    }

    /**
     * Calculates which day of the week game falls on
     * returns day id
     *
     * @return int
     */
    public function calculateDay()
    {
        return date('N', strtotime($this->getDate())) - 1;
    }

    /**
     * Calculates the date of the game
     *
     * @return string
     */
    public function calculateDate()
    {
        return YBCore_Utility_DateTime::getNextGameDateTime();
    }

    /**
     * Sets the $_status.
     *
     * @param int $status 
     */
    public function setStatus($status)
    {
        $this->_status = (int) $status;
    }

    /**
     * Gets the $_status.
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->_status;
    }

}