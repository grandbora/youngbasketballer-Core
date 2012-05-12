<?php
/**
 * Abstract class for YBCore Model User Schedule classes.
 */
abstract class YBCore_Model_User_Schedule_Abstract extends YBCore_Schedule_Abstract
{

    /**
     * Gets the team Id of the owner
     * which is owner's team's Id
     * 
     * @return int
     */
    protected function _getOwnerTeamId()
    {
        $user = $this->_getOwner();
        return $user->getTeamId();
    }

    /**
     * Gets list of upcoming trainings of the owner
     * creates training events and returns them
     *
     * @return array of YBCore_Model_Event_Training
     */
    protected function _getTrainingList()
    {
        $eventList = array();
        $user = $this->_getOwner();
        
        foreach ($user->getContractList() as $contract)
            foreach ($contract->getDayList() as $contractDay)
                $eventList[] = new YBCore_Model_Event_Training($contract, $contractDay);
        
        return $eventList;
    }
}