<?php
/**
 * Class for TeamOwner Schedule.
 */
class YBCore_Model_User_Schedule_TeamOwner extends YBCore_Model_User_Schedule_Abstract
{

    /**
     * Gets the list of upcoming trainings;
     * overridden because TO doesnt have trainings
     */
    protected function _getTrainingList()
    {
        throw new YBCore_Exception_Unbelievable();
    }

    /**
     * Loads gameList to the eventCollection
     * overridden because TO doesnt have trainings
     *
     */
    public function loadAllEventList()
    {
        $this->getEventCollection()->clear();
        $this->getEventCollection()->addEvents($this->_getScheduledGameList());
    }
}