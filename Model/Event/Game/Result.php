<?php
/**
 */
class YBCore_Model_Event_Game_Result extends YBCore_Model_Abstract
{

    /**
     * id of the game result 
     *
     * @var int
     */
    private $_id;

    /**
     * game date
     *
     * @var string
     */
    private $_gameDate;

    /**
     * id of the game 
     *
     * @var int
     */
    private $_gameId;

    /**
     * id of the winnerTeam
     *
     * @var int
     */
    private $_winnerTeamId;

    /**
     * winnerTeam
     *
     * @var YBCore_Model_Team
     */
    private $_winnerTeam;

    /**
     * id of the loserTeam
     *
     * @var int
     */
    private $_loserTeamId;

    /**
     * loserTeam
     *
     * @var YBCore_Model_Team
     */
    private $_loserTeam;

    /**
     * winnerScore  
     *
     * @var int
     */
    private $_winnerScore;

    /**
     * loserScore  
     *
     * @var int
     */
    private $_loserScore;

    /**
     * revenue 
     *
     * @var int
     */
    private $_revenue;

    /**
     * Sets the $_id.
     *
     * @param $id int
     */
    public function setId($id)
    {
        $this->_id = (int) $id;
    }

    /**
     * Gets the $_id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Sets the $_gameDate.
     *
     * @param string $gameDate
     */
    public function setGameDate($gameDate)
    {
        $gameDate = explode(' ', $gameDate);
        $this->_gameDate = $gameDate[0];
    }

    /**
     * Gets the $_gameDate.
     *
     * @return string
     */
    public function getGameDate()
    {
        return $this->_gameDate;
    }

    /**
     * Sets the $_gameId.
     *
     * @param int $gameId 
     */
    public function setGameId($gameId)
    {
        $this->_gameId = (int) $gameId;
    }

    /**
     * Gets the $_gameId.
     *
     * @return int
     */
    public function getGameId()
    {
        return $this->_gameId;
    }

    /**
     * Sets the $_winnerTeamId.
     *
     * @param int $winnerTeamId 
     */
    public function setWinnerTeamId($winnerTeamId)
    {
        $this->_winnerTeamId = (int) $winnerTeamId;
    }

    /**
     * Gets the $_winnerTeamId.
     *
     * @return int
     */
    public function getWinnerTeamId()
    {
        return $this->_winnerTeamId;
    }

    /**
     * Gets the $_winnerTeam.
     *
     * @return YBCore_Model_Team
     */
    public function getWinnerTeam()
    {
        if (null === $this->_winnerTeam)
            $this->_winnerTeam = new YBCore_Model_Team($this->getWinnerTeamId());
        
        return $this->_winnerTeam;
    }

    /**
     * Sets the $_loserTeamId.
     *
     * @param int $loserTeamId 
     */
    public function setLoserTeamId($loserTeamId)
    {
        $this->_loserTeamId = (int) $loserTeamId;
    }

    /**
     * Gets the $_loserTeamId.
     *
     * @return int
     */
    public function getLoserTeamId()
    {
        return $this->_loserTeamId;
    }

    /**
     * Gets the $_loserTeam.
     *
     * @return YBCore_Model_Team
     */
    public function getLoserTeam()
    {
        if (null === $this->_loserTeam)
            $this->_loserTeam = new YBCore_Model_Team($this->getLoserTeamId());
        
        return $this->_loserTeam;
    }

    /**
     * Sets the $_winnerScore.
     *
     * @param int $winnerScore 
     */
    public function setWinnerScore($winnerScore)
    {
        $this->_winnerScore = (int) $winnerScore;
    }

    /**
     * Gets the $_winnerScore.
     *
     * @return int
     */
    public function getWinnerScore()
    {
        return $this->_winnerScore;
    }

    /**
     * Sets the $_loserScore.
     *
     * @param int $loserScore 
     */
    public function setLoserScore($loserScore)
    {
        $this->_loserScore = (int) $loserScore;
    }

    /**
     * Gets the $_loserScore.
     *
     * @return int
     */
    public function getLoserScore()
    {
        return $this->_loserScore;
    }

    /**
     * Sets the $_revenue.
     *
     * @param int $revenue 
     */
    public function setRevenue($revenue)
    {
        $this->_revenue = (int) $revenue;
    }

    /**
     * Gets the $_revenue.
     *
     * @return int
     */
    public function getRevenue()
    {
        return $this->_revenue;
    }
}