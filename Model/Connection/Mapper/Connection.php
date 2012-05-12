<?php
/**
 * Connection Mapper parent class
 * 
 */
class YBCore_Model_Connection_Mapper_Connection extends YBCore_Mapper_Abstract
{
    /**
     * Connection types 
     */
    const TYPE_TEAM = 1;
    const TYPE_INDIVIDUAL = 2;

    /**
     */
    public function __construct()
    {
        $mapList = array();
        $mapList[] = new YBCore_Mapper_Map("id", "id", self::DATATYPE_INT);
        $mapList[] = new YBCore_Mapper_Map("employer", "employer", self::DATATYPE_OBJECT);
        $mapList[] = new YBCore_Mapper_Map("employee", "employee", self::DATATYPE_OBJECT);
        $mapList[] = new YBCore_Mapper_Map("salary", "salary", self::DATATYPE_INT);
        $mapList[] = new YBCore_Mapper_Map("type", "type", self::DATATYPE_INT);
        $mapList[] = new YBCore_Mapper_Map("status", "status", self::DATATYPE_INT);
        $this->_setMapList($mapList);
        $this->_setDbTableClassName('YBCore_Model_Connection_DbTable_Connection');
    }

    /**
     * Gets the $_modelClassName.
     * $entity parameter used only in overridden methods
     * in order to decide which model to be  instantiated
     *
     * @param dbRow $entity
     * @return string
     */
    protected function _getModelClassName($entity)
    {
        switch ($entity->status)
        {
            case YBCore_Model_Connection_Mapper_Contract::STATUS:
                return 'YBCore_Model_Connection_Contract';
            case YBCore_Model_Connection_Mapper_Offer::STATUS:
                return 'YBCore_Model_Connection_Offer';
            case YBCore_Model_Connection_Mapper_Request::STATUS:
                return 'YBCore_Model_Connection_Request';
        }
    }

    /**
     * Loads the connections of the given player
     *
     * @param int|array $id
     * @return array of of YBCore_Model_Connection_Abstract
     */
    public function loadPlayerConnectionList($id)
    {
        $employeeClause = $this->_createWhereClause($id, 'employee');
        $employerClause = array();
        $employerClause[] = $this->_createWhereClause($id, 'employer');
        $employerClause[] = $this->_createWhereClause(self::TYPE_INDIVIDUAL, 'type');
        $employerClause = $this->_combineWhereClauseList($employerClause);
        $final = $this->_combineWhereClauseList(array($employeeClause, $employerClause), self::LOGICOPERATOR_OR);
        
        $this->_addToOrderClauseList('type');
        $this->_setWhereClauseList($final);
        return $this->_loadModelList();
    }

    /**
     * Loads the connections of the given coach 
     *
     * @param int|array $userId
     * @return array of of YBCore_Model_Connection_Abstract
     */
    public function loadCoachConnectionList($userId)
    {
        $employeeClause = $this->_createWhereClause($userId, 'employee');
        $this->_setWhereClauseList($employeeClause);
        return $this->_loadModelList();
    }

    /**
     * Loads the connections of the given team 
     *
     * @param int|array $teamId
     * @return array of of YBCore_Model_Connection_Abstract
     */
    public function loadTeamConnectionList($teamId)
    {
        $employerClause = array();
        $employerClause[] = $this->_createWhereClause($teamId, 'employer');
        $employerClause[] = $this->_createWhereClause(self::TYPE_TEAM, 'type');
        $employerClause = $this->_combineWhereClauseList($employerClause);
        
        $this->_setWhereClauseList($employerClause);
        return $this->_loadModelList();
    }
}