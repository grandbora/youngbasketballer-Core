<?php
/**
 * User abstract mapper class.
 */
class YBCore_Model_User_Mapper_User extends YBCore_Mapper_Abstract
{
    /**
     * User Id types 
     */
    const USERIDTYPE_ID = 'id';
    const USERIDTYPE_FBID = 'fbId';

    /**
     * DbTableClassName is overriden by _setDbTableClassName method due to different types of players
     * which all use the same db table
     */
    public function __construct()
    {
        $mapList = array();
        $mapList[] = new YBCore_Mapper_Map("id", "id", self::DATATYPE_INT);
        $mapList[] = new YBCore_Mapper_Map("fbId", "fb_id", self::DATATYPE_INT);
        $mapList[] = new YBCore_Mapper_Map("type", "type", self::DATATYPE_INT);
        $mapList[] = new YBCore_Mapper_Map("position", "position", self::DATATYPE_INT);
        $mapList[] = new YBCore_Mapper_Map("lineUp", "line_up", self::DATATYPE_INT);
        $mapList[] = new YBCore_Mapper_Map("balance", "balance", self::DATATYPE_INT);
        
        $mapList[] = new YBCore_Mapper_Map("fbName", "name");
        $mapList[] = new YBCore_Mapper_Map("fbFirstName", "first_name");
        $mapList[] = new YBCore_Mapper_Map("fbMiddleName", "middle_name");
        $mapList[] = new YBCore_Mapper_Map("fbLastName", "last_name");
        $mapList[] = new YBCore_Mapper_Map("fbLink", "link");
        
        $this->_setMapList($mapList);
        $this->_setDbTableClassName('YBCore_Model_User_DbTable_User');
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
        switch ($entity->type)
        {
            case YBCore_Model_User_Mapper_Player::TYPE:
                return 'YBCore_Model_User_Player';
            case YBCore_Model_User_Mapper_Coach::TYPE:
                return 'YBCore_Model_User_Coach';
            case YBCore_Model_User_Mapper_TeamOwner::TYPE:
                return 'YBCore_Model_User_TeamOwner';
        }
    }

    /**
     * Loads the user(s) of the given id(s) (if any)
     *
     * @param int|array $id
     * @return array of of YBCore_Model_User_Abstract
     */
    public function loadUserList($id = array())
    {
        $type = constant(get_class($this) . '::TYPE');
        $whereClause = $this->_createWhereClause($type, 'type');
        if (false === empty($id))
        {
            $idClause = $this->_createWhereClause($id, 'id');
            $whereClause = $this->_combineWhereClauseList(array($whereClause, $idClause));
        }
        $this->_setWhereClauseList($whereClause);
        return $this->_loadModelList();
    }

    /**
     * function to initialize a user either by id OR fbId
     * 
     * @param  array of int | int $value
     * @param [optional] self::USERIDTYPE_ID|self::USERIDTYPE_FBID $type = self::USERIDTYPE_ID   
     * @return array of YBCore_Model_User_Abstract
     */
    public function initializeUser($value, $type = self::USERIDTYPE_ID)
    {
        //set where clause
        $whereClause = $this->_createWhereClause($value, $type);
        $this->_setWhereClauseList($whereClause);
        
        // set order clause
        $this->_addToOrderClauseList('type');
        $this->_addToOrderClauseList('position');
        $this->_addToOrderClauseList('lineUp');
        
        return $this->_loadModelList();
    }
}