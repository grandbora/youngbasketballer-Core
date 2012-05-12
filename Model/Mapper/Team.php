<?php
/**
 * Mapper class for team 
 */
class YBCore_Model_Mapper_Team extends YBCore_Mapper_Abstract
{

    public function __construct()
    {
        $mapList = array();
        $mapList[] = new YBCore_Mapper_Map("id", "id", self::DATATYPE_INT);
        $mapList[] = new YBCore_Mapper_Map("name", "name");
        $mapList[] = new YBCore_Mapper_Map("owner", "owner", self::DATATYPE_OBJECT);
        $this->_setMapList($mapList);
    }

    /**
     * Loads the connections of the team
     *
     * @param int|array $teamId
     * @return array of of YBCore_Model_Connection_Abstract
     */
    public function loadConnectionList($teamId)
    {
        $connectionMapper = YBCore_Mapper_Factory::getMapper('YBCore_Model_Connection_Abstract');
        return $connectionMapper->loadTeamConnectionList($teamId);
    }

    /**
     * Loads the list of teams of the given owner id(s)
     *
     * @param int|array $ownerId
     * @return array of YBCore_Model_Team
     */
    public function loadTeamListByOwner($ownerId)
    {
        $whereClause = $this->_createWhereClause($ownerId, "owner");
        $this->_setWhereClauseList($whereClause);
        return $this->_loadModelList();
    }

    /**
     * Loads all the teams
     *
     * @return array of YBCore_Model_Team
     */
    public function loadTeamList()
    {
        return $this->_loadModelList();
    }
}