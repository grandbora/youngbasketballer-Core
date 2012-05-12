<?php
/**
 */
class YBCore_Model_Event_Game_Mapper_Result extends YBCore_Mapper_Abstract
{

    public function __construct()
    {
        $mapList = array();
        $mapList[] = new YBCore_Mapper_Map("id", "id", self::DATATYPE_INT);
        $mapList[] = new YBCore_Mapper_Map("gameDate", "created");
        $mapList[] = new YBCore_Mapper_Map("gameId", "game", self::DATATYPE_INT);
        $mapList[] = new YBCore_Mapper_Map("winnerTeamId", "winner", self::DATATYPE_INT);
        $mapList[] = new YBCore_Mapper_Map("loserTeamId", "loser", self::DATATYPE_INT);
        $mapList[] = new YBCore_Mapper_Map("winnerScore", "winner_score", self::DATATYPE_INT);
        $mapList[] = new YBCore_Mapper_Map("loserScore", "loser_score", self::DATATYPE_INT);
        $mapList[] = new YBCore_Mapper_Map("revenue", "revenue", self::DATATYPE_INT);
        $this->_setMapList($mapList);
    }

    /**
     * Gets list of game results for the given teamId(s)
     *
     * @param [optional] int|array $teamId = array()
     * @return array of YBCore_Model_Event_Game_Result
     */
    public function loadGameResultListByTeamId($teamId = array())
    {
        $winnerClause = $this->_createWhereClause($teamId, 'winnerTeamId');
        $loserClause = $this->_createWhereClause($teamId, 'loserTeamId');
        $finalClause = $this->_combineWhereClauseList(array($winnerClause, $loserClause), self::LOGICOPERATOR_OR);
        $this->_setWhereClauseList($finalClause);
        
        $this->_addToOrderClauseList('gameDate', self::SORTDIRECTION_DESC);
        
        return $this->_loadModelList();
    }
}