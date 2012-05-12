<?php
/**
 * TeamOwner mapper class.
 */
class YBCore_Model_User_Mapper_TeamOwner extends YBCore_Model_User_Mapper_User
{
    const TYPE = 3;

    /**
     * Gets the team whose owner id is given
     *
     * @param int $ownerId
     * @return YBCore_Model_Team
     */
    public function getTeamByOwner($ownerId)
    {
        $teamMapper = YBCore_Mapper_Factory::getMapper('YBCore_Model_Team');
        $teamList = $teamMapper->loadTeamListByOwner($ownerId);
        
        $team = null;
        if (false === empty($teamList))
            $team = $teamList[0];
        
        return $team;
    }
}