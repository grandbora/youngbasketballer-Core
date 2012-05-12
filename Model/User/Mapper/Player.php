<?php
/**
 * Player mapper class.
 */
class YBCore_Model_User_Mapper_Player extends YBCore_Model_User_Mapper_User
{
    const TYPE = 1;
    
    /**
     * Player position
     */
    const POSITION_GUARD = 1;
    const POSITION_FORWARD = 2;
    const POSITION_CENTER = 3;

    /**
     * Loads the connections of the given model
     *
     * @param int|array $id
     * @return array of YBCore_Model_Connection_Abstract
     */
    public function loadConnectionList($id)
    {
        $connectionMapper = YBCore_Mapper_Factory::getMapper('YBCore_Model_Connection_Abstract');
        return $connectionMapper->loadPlayerConnectionList($id);
    }
}