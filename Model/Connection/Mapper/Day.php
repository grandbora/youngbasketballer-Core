<?php
/**
 * Connection day Mapper class
 * 
 */
class YBCore_Model_Connection_Mapper_Day extends YBCore_Mapper_Abstract
{

    public function __construct()
    {
        $mapList = array();
        $mapList[] = new YBCore_Mapper_Map("id", "id", self::DATATYPE_INT);
        $mapList[] = new YBCore_Mapper_Map("connectionId", "connection", self::DATATYPE_INT);
        $mapList[] = new YBCore_Mapper_Map("dayId", "day", self::DATATYPE_INT);
        $this->_setMapList($mapList);
    }

    /**
     * Loads the days of the given connections 
     *
     * @param int|array $connectionId
     * @return array of of YBCore_Model_Connection_Day
     */
    public function loadByConnectionId($connectionId)
    {
        $whereClause = $this->_createWhereClause($connectionId, 'connectionId');
        $this->_setWhereClauseList($whereClause);
        
        $this->_addToOrderClauseList('dayId');
        return $this->_loadModelList();
    }

    /**
     * Deletes the days of the given connections 
     *
     * @param int|array $connectionId
     */
    public function deleteByConnectionId($connectionId)
    {
        $whereClause = $this->_createWhereClause($connectionId, 'connectionId');
        $this->_setWhereClauseList($whereClause);
        return $this->_deleteModelList();
    }
}