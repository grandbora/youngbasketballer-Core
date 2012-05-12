<?php
/**
 */
class YBCore_Model_Event_Game_Mapper_Game extends YBCore_Mapper_Abstract
{

    public function __construct()
    {
        $mapList = array();
        $mapList[] = new YBCore_Mapper_Map("id", "id", self::DATATYPE_INT);
        $mapList[] = new YBCore_Mapper_Map("homeTeam", "home", self::DATATYPE_OBJECT);
        $mapList[] = new YBCore_Mapper_Map("awayTeam", "away", self::DATATYPE_OBJECT);
        $mapList[] = new YBCore_Mapper_Map("status", "status");
        $this->_setMapList($mapList);
        $this->_setDbTableClassName('YBCore_Model_Event_Game_DbTable_Game');
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
            case YBCore_Model_Event_Game_Mapper_ScheduledGame::STATUS:
                return 'YBCore_Model_Event_Game_ScheduledGame';
            case YBCore_Model_Event_Game_Mapper_Challenge::STATUS:
                return 'YBCore_Model_Event_Game_Challenge';
        }
    }

    /**
     * Sets the given entity to the model
     *
     * @param YBCore_Model_Abstract $model
     * @param dbRow $entity
     */
    protected function _setEntityToModel(YBCore_Model_Event_Game_Abstract $model, $entity)
    {
        parent::_setEntityToModel($model, $entity);
        
        // set game Date        
        $date = $model->calculateDate();
        $model->setDate($date);
        
        // set game Day
        $day = $model->calculateDay();
        $model->setDay($day);
    }

    /**
     * Gets list of upcoming (scheduled games OR challenges) of (the team whose id is given OR all teams)
     *
     * @param int|array YBCore_Model_Event_Game_Mapper_*::STATUS $status = array()
     * @param [optional] int|array $teamId = array()
     * @return array of YBCore_Model_Event_Game_Abstract
     */
    public function loadGameList($status, $teamId = array())
    {
        $finalClause = $this->_createWhereClause($status, 'status');
        
        if (false === empty($teamId))
        {
            $whereClause = array();
            $whereClause[] = $this->_createWhereClause($teamId, 'homeTeam');
            $whereClause[] = $this->_createWhereClause($teamId, 'awayTeam');
            $whereClause = $this->_combineWhereClauseList($whereClause, self::LOGICOPERATOR_OR);
            $finalClause = $this->_combineWhereClauseList(array($whereClause, $finalClause));
        }
        
        $this->_setWhereClauseList($finalClause);
        return $this->_loadModelList();
    }

    /**
     * Gets the game whose id and status given
     *
     * @param int $gameId
     * @param YBCore_Model_Event_Game_Mapper_*::STATUS $status
     * @return array of YBCore_Model_Event_Game
     */
    public function loadGameById($gameId, $status)
    {
        $whereClause = array();
        $whereClause[] = $this->_createWhereClause($gameId, 'id');
        $whereClause[] = $this->_createWhereClause($status, 'status');
        $whereClause = $this->_combineWhereClauseList($whereClause);
        
        $this->_setWhereClauseList($whereClause);
        return $this->_loadModelList();
    }
}