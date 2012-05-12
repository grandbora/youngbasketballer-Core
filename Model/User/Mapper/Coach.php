<?php
/**
 * Coach mapper class.
 */
class YBCore_Model_User_Mapper_Coach extends YBCore_Model_User_Mapper_User
{
    const TYPE = 2;
    
    /**
     * Coach position
     */
    const POSITION_HEADCOACH = 1;
    const POSITION_ASSISTANTCOACH = 2;

    /**
     * Loads the connections of the coach
     *
     * @param int|array $userId
     * @return array of of YBCore_Model_Connection_Abstract
     */
    public function loadConnectionList($userId)
    {
        $connectionMapper = YBCore_Mapper_Factory::getMapper('YBCore_Model_Connection_Abstract');
        return $connectionMapper->loadCoachConnectionList($userId);
    }
}