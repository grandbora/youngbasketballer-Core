<?php
/**
 * Abstract class for YBCore Schedule classes.
 */
abstract class YBCore_Schedule_Abstract extends YBCore_Abstract
{

    /**
     * List of events to be scheduled
     *
     * @var YBCore_Model_Event_Collection
     */
    private $_eventCollection;

    /**
     * List of completed games
     *
     * @var array of YBCore_Model_Event_Game_CompletedGame
     */
    private $_completedGameList;

    /**
     * List of upcoming games
     *
     * @var array of YBCore_Model_Event_Game_ScheduledGame
     */
    private $_scheduledGameList;

    /**
     * Challenge list 
     *
     * @var YBCore_Model_Event_Game_Challenge
     */
    private $_challengeList;

    /**
     * Owner object of the schedule
     *
     * @var YBCore_Model_User_Abstract | YBCore_Model_Team 
     */
    private $_owner;

    /**
     * a multidimesional array holds the an array for each day
     *
     * @var md array of YBCore_Model_Event_Abstract
     */
    private $_weekArray = array(array('day' => 0, 'event' => array()), array('day' => 1, 'event' => array()), array('day' => 2, 'event' => array()), array('day' => 3, 'event' => array()), 
    array('day' => 4, 'event' => array()), array('day' => 5, 'event' => array()), array('day' => 6, 'event' => array()));

    /**
     * @param YBCore_Model_User_Abstract | YBCore_Model_Team $owner
     */
    public function __construct(YBCore_Model_Abstract $owner)
    {
        $this->_setOwner($owner);
        $this->_setEventCollection(new YBCore_Model_Event_Collection());
    }

    /**
     * Sets the eventCollection
     *
     * @param YBCore_Model_Event_Collection $eventCollection
     */
    private function _setEventCollection($eventCollection)
    {
        $this->_eventCollection = $eventCollection;
    }

    /**
     * Loads the eventCollection and returns it
     *
     * @return YBCore_Model_Event_Collection
     */
    public function getEventCollection()
    {
        return $this->_eventCollection;
    }

    /**
     * Loads gameList and trainingList to the eventCollection
     * 
     */
    public function loadAllEventList()
    {
        $this->getEventCollection()->clear();
        $this->getEventCollection()
            ->addEvents($this->_getScheduledGameList())
            ->addEvents($this->_getTrainingList());
    }

    /**
     * Loads the gameList of given statuses to eventCollection
     *
     * @param int YBCore_Model_Event_Game_Mapper_*::STATUS
     */
    public function loadGameEventList()
    {
        $this->getEventCollection()->clear();
        
        $neededGameStatuses = func_get_args();
        if (in_array(YBCore_Model_Event_Game_Mapper_Challenge::STATUS, $neededGameStatuses, true))
            $this->getEventCollection()->addEvents($this->_getChallengeList());
        
        if (in_array(YBCore_Model_Event_Game_Mapper_ScheduledGame::STATUS, $neededGameStatuses, true))
            $this->getEventCollection()->addEvents($this->_getScheduledGameList());
        
        if (in_array(YBCore_Model_Event_Game_Mapper_CompletedGame::STATUS, $neededGameStatuses, true))
            $this->getEventCollection()->addEvents($this->_getCompletedGameList());
    }

    /**
     * Loads the trainingList to eventCollection
     *
     */
    public function loadTrainingEventList()
    {
        $this->getEventCollection()->clear();
        $this->getEventCollection()->addEvents($this->_getTrainingList());
    }

    /**
     * Clears the gameList of the team according to given status.
     *
     * @param [optional] YBCore_Model_Event_Game_Mapper_*::STATUS $status = null
     */
    public function clearGameList($status = null)
    {
        switch ($status)
        {
            case YBCore_Model_Event_Game_Mapper_Challenge::STATUS:
                $this->_challengeList = null;
                break;
            case YBCore_Model_Event_Game_Mapper_ScheduledGame::STATUS:
                $this->_scheduledGameList = null;
                break;
            case YBCore_Model_Event_Game_Mapper_CompletedGame::STATUS:
                $this->_completedGameList = null;
                break;
            default:
                $this->_challengeList = null;
                $this->_scheduledGameList = null;
                $this->_completedGameList = null;
                break;
        }
    }

    /**
     * Sets the $_completedGameList.
     *
     * @param array of YBCore_Model_Event_Game_CompletedGame $completedGameList
     */
    private function _setCompletedGameList(array $completedGameList)
    {
        $this->_completedGameList = $completedGameList;
    }

    /**
     * Sets the $_scheduledGameList.
     *
     * @param array of YBCore_Model_Event_Game_ScheduledGame $scheduledGameList
     */
    private function _setScheduledGameList(array $scheduledGameList)
    {
        $this->_scheduledGameList = $scheduledGameList;
    }

    /**
     * Sets the $_challengeList.
     *
     * @param array of YBCore_Model_Event_Game_Challenge $challengeList
     */
    private function _setchallengeList(array $challengeList)
    {
        $this->_challengeList = $challengeList;
    }

    /**
     * Gets list of completed games of the team 
     * 
     * @return array of YBCore_Model_Event_Game_CompletedGame
     */
    protected function _getCompletedGameList()
    {
        if (null === $this->_completedGameList)
        {
            $teamId = $this->_getOwnerTeamId();
            if (null === $teamId)
                $this->_setCompletedGameList(array());
            else
                $this->_loadGameList($teamId, YBCore_Model_Event_Game_Mapper_CompletedGame::STATUS);
        }
        return $this->_completedGameList;
    }

    /**
     * Gets list of upcoming games of the team 
     * 
     * @return array of YBCore_Model_Event_Game_ScheduledGame
     */
    protected function _getScheduledGameList()
    {
        if (null === $this->_scheduledGameList)
        {
            $teamId = $this->_getOwnerTeamId();
            if (null === $teamId)
                $this->_setScheduledGameList(array());
            else
                $this->_loadGameList($teamId, YBCore_Model_Event_Game_Mapper_ScheduledGame::STATUS);
        }
        return $this->_scheduledGameList;
    }

    /**
     * Gets the challengeList of the team 
     * 
     * @return array of YBCore_Model_Event_Game_Challenge
     */
    protected function _getChallengeList()
    {
        if (null === $this->_challengeList)
        {
            $teamId = $this->_getOwnerTeamId();
            if (null === $teamId)
                $this->_setchallengeList(array());
            else
                $this->_loadGameList($teamId, YBCore_Model_Event_Game_Mapper_Challenge::STATUS);
        }
        return $this->_challengeList;
    }

    /**
     * Loads the list of games
     * 
     * @param int $teamId
     * @param int YBCore_Model_Event_Game_Mapper_*::STATUS $status
     */
    private function _loadGameList($teamId, $status)
    {
        $gameMapper = YBCore_Mapper_Factory::getMapper('YBCore_Model_Event_Game_Abstract');
        $gameList = $gameMapper->loadGameList($status, $teamId);
        
        switch ($status)
        {
            case YBCore_Model_Event_Game_Mapper_CompletedGame::STATUS:
                $this->_setCompletedGameList($gameList);
                break;
            case YBCore_Model_Event_Game_Mapper_ScheduledGame::STATUS:
                $this->_setScheduledGameList($gameList);
                break;
            case YBCore_Model_Event_Game_Mapper_Challenge::STATUS:
                $this->_setchallengeList($gameList);
                break;
        }
    }

    /**
     * Gets list of upcoming trainings
     * 
     * @return array of YBCore_Model_Event_Training
     */
    abstract protected function _getTrainingList();

    /**
     * Sets the $_owner.
     *
     * @param YBCore_Model_User_Abstract | YBCore_Model_Team $owner
     */
    private function _setOwner(YBCore_Model_Abstract $owner)
    {
        $this->_owner = $owner;
    }

    /**
     * Gets the $_owner.
     *
     * @return YBCore_Model_User_Abstract | YBCore_Model_Team
     */
    protected function _getOwner()
    {
        return $this->_owner;
    }

    /**
     * Gets the team Id of the owner
     * 
     * @return int
     */
    abstract protected function _getOwnerTeamId();

    /**
     * Gets the weekly training of the user
     * starts from monday 
     * 
     * @return md array of YBCore_Model_Event_Training
     */
    public function getTrainingProgram()
    {
        $this->loadTrainingEventList();
        return $this->_setEventCollectionToWeekArray();
    }

    /**
     * Gets the upcoming events of the user
     * starts from current day, shows events of next 7 days 
     * 
     * @return md array of YBCore_Model_Event_Abstract
     */
    public function getUpcomingEvents()
    {
        $this->loadAllEventList();
        $resultArray = $this->_setEventCollectionToWeekArray();
        
        // shift the array until reaching to current day
        $currentDay = YBCore_Utility_DateTime::getCurrentDayId();
        for ($i = 0; $i < $currentDay; $i++)
            $resultArray[] = array_shift($resultArray);
        
        return $resultArray;
    }

    /**
     * Fills the weekArray with the events in eventCollection 
     * 
     * @return md array of YBCore_Model_Event_Abstract
     */
    private function _setEventCollectionToWeekArray()
    {
        // preserve original array
        $resultArray = $this->_weekArray;
        
        // first loop through trainings
        foreach ($this->getEventCollection() as $training)
        {
            if ($training instanceof YBCore_Model_Event_Training)
                switch ($training->getType())
                {
                    case YBCore_Model_Event_Training::TYPE_TEAMTRAINING: // add to start
                        array_unshift($resultArray[$training->getDay()]['event'], $training);
                        break;
                    case YBCore_Model_Event_Training::TYPE_INDIVIDUALTRAINING: // add to end
                        $resultArray[$training->getDay()]['event'][] = $training;
                        break;
                }
        }
        
        // then loop through games
        foreach ($this->getEventCollection() as $game)
        {
            if ($game instanceof YBCore_Model_Event_Game_Abstract) // always add to start
                array_unshift($resultArray[$game->getDay()]['event'], $game);
        }
        
        return $resultArray;
    }
}