<?php
/**
 * TeamOwner class
 * 
 */
class YBCore_Model_User_TeamOwner extends YBCore_Model_User_Abstract implements YBCore_Interface_Schedule
{

    /**
     * TO doesnt have any connection
     *
     * @return array
     */
    protected function _getConnectionList()
    {
        throw new YBCore_Exception_Unbelievable();
    }

    /**
     * Creates a connection to user whose id is given.
     * TO makes an offer to a player or coach
     * if coach offer add connection day
     * Check if team already employs $targetUserId
     * Check if an offer already made to targetUser
     * 
     * @todo check if an offer already made to targetUser
     * @throws YBCore_Exception_Authentication
     * @throws YBCore_Exception_Logic
     * @param int $targetUserId
     * @param array $dayList
     * @param int $salary
     */
    public function createConnection($targetUserId, array $dayList, $salary)
    {
        // verify user exists
        $targetUser = $this->_getUser($targetUserId);
        
        // target user must be an employee (player|coach)
        if (false === $targetUser instanceof YBCore_Interface_Employee)
            throw new YBCore_Exception_Logic();
        
        // no need to load team from db, all necessary data is available
        $team = new YBCore_Model_Team();
        $team->setId($this->getTeamId());
        $team->setOwnerId($this->getId());
        
        // target user must not be a current employee of the team
        if (in_array($targetUser->getId(), $team->getEmployeeIdList()))
            throw new YBCore_Exception_Logic();
        
     // target user must not be offered a contract already
        if (in_array($targetUser->getId(), $team->getOfferIdList()))
            throw new YBCore_Exception_Logic();
        
     // if target is a coach, then team must not employ any other coach(es)
        $coachList = $team->getCoachList();
        if ($targetUser instanceof YBCore_Model_User_Coach && false === empty($coachList))
            throw new YBCore_Exception_Logic();
        
        $newContract = new YBCore_Model_Connection_Offer();
        $newContract->setEmployerId($this->getTeamId());
        $newContract->setEmployeeId($targetUser->getId());
        $newContract->setSalary($salary);
        $newContract->setType(YBCore_Model_Connection_Mapper_Offer::TYPE_TEAM);
        
        // set day list if target user is coach
        if ($targetUser instanceof YBCore_Model_User_Coach)
        {
            $dayModelList = array();
            foreach ($dayList as $dayIndex)
            {
                $day = new YBCore_Model_Connection_Day();
                $day->setDayId($dayIndex);
                $dayModelList[] = $day;
            }
            $newContract->setDayList($dayModelList);
        }
        
        $team->saveSecureConnection($newContract);
    }

    /**
     * Removes the given connection.
     * TO cancels a team contract, dismiss requests for that user are dropped  
     * TO withdraws a team offer 
     * TO refuses a coach request
     * 
     * @throws YBCore_Exception_Authentication
     * @param int|string $connectionId
     */
    public function removeConnection($connectionId)
    {
        $team = $this->getTeam();
        // verify connection
        $connection = $team->verifyConnection($connectionId);
        
        // drop all dismiss requests for this employee
        // if it is a headcoach drop all requests
        if (YBCore_Model_Connection_Mapper_Contract::STATUS === $connection->getStatus())
            foreach ($team->getConnectionListByStatus(YBCore_Model_Connection_Mapper_Request::STATUS) as $request)
                if ($connection->getEmployeeId() === $request->getEmployeeId() || true === $connection->getEmployee() instanceof YBCore_Model_User_Coach)
                    $team->removeSecureConnection($request);
        
        $team->removeSecureConnection($connection);
    }

    /**
     * Accepts the given challenge.
     * If team already has a scheduled game, cannot accept a challenge
     * If team already has challenge delete it
     *
     * @throws YBCore_Exception_Authentication
     * @throws YBCore_Exception_Logic
     * @param int $gameId
     */
    public function acceptChallenge($gameId)
    {
        $team = $this->getTeam();
        $teamSchedule = $team->getSchedule();
        
        // check if any scheduled game exists
        $teamSchedule->loadGameEventList(YBCore_Model_Event_Game_Mapper_ScheduledGame::STATUS);
        if (false === $teamSchedule->getEventCollection()->isEmpty())
            throw new YBCore_Exception_Logic();
        
        $game = $team->verifyGame($gameId, YBCore_Model_Event_Game_Mapper_Challenge::STATUS);
        
        // delete any existing challenges
        $teamSchedule->loadGameEventList(YBCore_Model_Event_Game_Mapper_Challenge::STATUS);
        $challengeList = $teamSchedule->getEventCollection();
        if (false === $challengeList->isEmpty())
            $team->removeSecureGame($challengeList[0]);
        
     // change the status and away team of the challenge and save
        $game->setAwayTeamId($team->getId());
        $game->setStatus(YBCore_Model_Event_Game_Mapper_ScheduledGame::STATUS);
        $team->saveSecureGame($game);
    }

    /**
     * Withdraws the given challenge.
     *
     * @throws YBCore_Exception_Authentication
     * @param int $gameId
     */
    public function withdrawChallenge($gameId)
    {
        $team = $this->getTeam();
        $game = $team->verifyGame($gameId, YBCore_Model_Event_Game_Mapper_Challenge::STATUS);
        
        $team->removeSecureGame($game);
    }

    /**
     * Creates a public challenge for TO's team
     * Can not create if already has a challenge or a scheduled game
     * 
     * @throws YBCore_Exception_Authentication
     * @throws YBCore_Exception_Logic
     */
    public function createChallenge()
    {
        $team = $this->getTeam();
        $teamSchedule = $team->getSchedule();
        
        // check for existing scheduled games and existing challenges 
        $teamSchedule->loadGameEventList(YBCore_Model_Event_Game_Mapper_ScheduledGame::STATUS, YBCore_Model_Event_Game_Mapper_Challenge::STATUS);
        if (false === $teamSchedule->getEventCollection()->isEmpty())
            throw new YBCore_Exception_Logic();
        
        $game = new YBCore_Model_Event_Game_Challenge();
        $game->setHomeTeamId($this->getTeamId());
        $team->saveSecureGame($game);
    }

    /**
     * Gets the $_teamId.
     *
     * @return int
     */
    public function getTeamId()
    {
        if (null !== $this->_teamId)
            return $this->_teamId;
        return $this->getTeam()->getId();
    }

    /**
     * Gets the $_team.
     *
     * @return YBCore_Model_Team
     */
    public function getTeam()
    {
        if (null === $this->_team)
        {
            $team = $this->_getMapper()->getTeamByOwner($this->getId());
            $this->_setTeam($team);
        }
        return $this->_team;
    }
}