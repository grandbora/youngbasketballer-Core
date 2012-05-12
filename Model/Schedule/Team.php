<?php
/**
 * @todo think about loading all type of games in single fetch 
 * (large number of completed games may create overhead)
 */
class YBCore_Model_Schedule_Team extends YBCore_Schedule_Abstract
{

    /**
     * Gets the team Id of the owner
     * which is owner Id
     * 
     * @return int
     */
    protected function _getOwnerTeamId()
    {
        $team = $this->_getOwner();
        return $team->getId();
    }

    /**
     * Gets list of upcoming trainings of the team
     * creates training events and returns them
     * 
     * It is assumed that all players have the same connection days
     *
     * @return array of YBCore_Model_Event_Training
     */
    protected function _getTrainingList()
    {
        $eventList = array();
        $team = $this->_getOwner();
        
        $contractList = $team->getContractList();
        if (false === empty($contractList))
        {
            $contract = $contractList[0];
            foreach ($contract->getDayList() as $contractDay)
                $eventList[] = new YBCore_Model_Event_Training($contract, $contractDay);
        }
        
        return $eventList;
    }
}