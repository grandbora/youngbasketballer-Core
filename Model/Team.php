<?php
/**
 * Class for YBCore Model Team.
 */
class YBCore_Model_Team extends YBCore_Model_Abstract implements YBCore_Interface_Employer
{

    /**
     * id of the team
     *
     * @var int
     */
    private $_id;

    /**
     * name of the team
     *
     * @var string
     */
    private $_name;

    /**
     * id of the owner of the team
     *
     * @var int
     */
    private $_ownerId;

    /**
     * owner of the team
     *
     * @var YBCore_Model_User_TeamOwner
     */
    private $_owner;

    /**
     * list of all  connections (contracts, offers and requests) of the team
     *
     * @var array of YBCore_Model_Connection_Abstract
     */
    private $_connectionList;

    /**
     * list of current contracts of the team
     *
     * @var array of YBCore_Model_Connection_Contract
     */
    private $_contractList;

    /**
     * list of current offers of the team
     *
     * @var array of YBCore_Model_Connection_Offer
     */
    private $_offerList;

    /**
     * list of current transfer requests of the team
     *
     * @var array of YBCore_Model_Connection_Request
     */
    private $_transferRequestList;

    /**
     * list of current dismiss requests of the team
     *
     * @var array of YBCore_Model_Connection_Request
     */
    private $_dismissRequestList;

    /**
     * schedule object of the team
     *
     * @var YBCore_Model_Schedule_Team
     */
    private $_schedule;

    /**
     * list of all the users related to this team
     * includes TO, Coach(es) and players 
     *
     * @var array of YBCore_Model_User_Abstract
     */
    private $_roster;

    /**
     * List of players sorted from $_roster,
     * sorted by type and position and Lineup
     *
     * @var md array of YBCore_Model_User_Abstract
     */
    private $_sortedRoster;

    /**
     * @param $id int[optional]
     */
    public function __construct($id = null)
    {
        if (null !== $id)
            $this->_load((int) $id);
    }

    /**
     * Sets the $_id.
     *
     * @param int $id
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
     * Sets the $_name.
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->_name = $name;
    }

    /**
     * Gets the $_name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Sets the $_ownerId.
     *
     * @param int $ownerId
     */
    public function setOwnerId($ownerId)
    {
        $this->_clearOwner();
        $this->_ownerId = (int) $ownerId;
    }

    /**
     * Gets the $_ownerId.
     *
     * @return int
     */
    public function getOwnerId()
    {
        return $this->_ownerId;
    }

    /**
     * Sets the $_owner.
     *
     * @param YBCore_Model_User_TeamOwner $owner
     */
    private function _setOwner(YBCore_Model_User_TeamOwner $owner)
    {
        $this->_owner = $owner;
    }

    /**
     * Gets the $_owner.
     *
     * @return YBCore_Model_User_TeamOwner
     */
    public function getOwner()
    {
        if (null === $this->_owner)
        {
            $ownerId = $this->getOwnerId();
            if (null !== $ownerId)
            {
                $owner = new YBCore_Model_User_TeamOwner($ownerId);
                $this->_setOwner($owner);
            }
        }
        return $this->_owner;
    }

    /**
     * Clears the $_owner.
     *
     */
    private function _clearOwner()
    {
        $this->_ownerId = null;
        $this->_owner = null;
    }

    /**
     * Sets the $_roster
     * 
     * @param array of YBCore_Model_User_Abstract $roster
     */
    private function _setRoster(array $roster)
    {
        $this->_roster = $roster;
    }

    /**
     * Gets the $_roster
     * 
     * @return array of YBCore_Model_User_Abstract
     */
    public function getRoster()
    {
        if (null === $this->_roster)
        {
            $userIdList = $this->getEmployeeIdList();
            $userIdList[] = $this->getOwnerId();
            
            $userMapper = YBCore_Mapper_Factory::getMapper('YBCore_Model_User_Abstract');
            $this->_setRoster($userMapper->initializeUser($userIdList));
        }
        return $this->_roster;
    }

    /**
     * Gets the sorted Roster of the Team
     * Sorts the roster by type position and lineUp
     * 
     * @return array of YBCore_Model_User_Abstract
     */
    public function getSortedRoster()
    {
        if (null === $this->_sortedRoster)
        {
            $this->_sortedRoster = array();
            foreach ($this->getRoster() as $user)
                $this->_insertUserToSortedRoster($user);
        }
        return $this->_sortedRoster;
    }

    /**
     * Inserts the given user to the sorted roster
     * 
     * @param YBCore_Model_User_Abstract $insertUser
     */
    private function _insertUserToSortedRoster(YBCore_Model_User_Abstract $insertUser)
    {
        if (true === empty($this->_sortedRoster))
        {
            $this->_sortedRoster[] = $insertUser;
            return;
        }
        
        for ($i = 0; $i < count($this->_sortedRoster); $i++)
        {
            $compareUser = $this->_sortedRoster[$i];
            if (true === $this->_isUserBefore($insertUser, $compareUser, array('type', 'position', 'lineUp')))
            {
                array_splice($this->_sortedRoster, $i, 0, $insertUser);
                return;
            }
        }
        
        // if reaches here should be appended to array
        $this->_sortedRoster[] = $insertUser;
    }

    /**
     * Compares given two users accroding to given filters
     * returns true if the source user should be inserted before the compare user 
     * 
     * @param YBCore_Model_User_Abstract $sourceUser
     * @param YBCore_Model_User_Abstract $compareUser
     * @param array of string $filterList
     */
    private function _isUserBefore(YBCore_Model_User_Abstract $sourceUser, YBCore_Model_User_Abstract $compareUser, array $filterList)
    {
        $currentFilter = array_shift($filterList);
        
        $userMapper = YBCore_Mapper_Factory::getMapper('YBCore_Model_User_Abstract');
        $currentMapping = $userMapper->getMap($currentFilter);
        $getterMethod = $currentMapping->getGetterMethodName();
        
        if ($sourceUser->$getterMethod() < $compareUser->$getterMethod())
            return true;
        elseif ($sourceUser->$getterMethod() === $compareUser->$getterMethod() && false == empty($filterList))
            return $this->_isUserBefore($sourceUser, $compareUser, $filterList);
        
        return false;
    }

    /**
     * Gets the id list of the current requested transfers
     * 
     * @return array
     */
    public function getRequestIdList()
    {
        $requestIdList = array();
        foreach (array_merge($this->getTransferRequestList(), $this->getDismissRequestList()) as $request)
            $requestIdList[] = $request->getEmployeeId();
        
        return $requestIdList;
    }

    /**
     * Gets the id list of the current players who are already offered a contract
     * 
     * @return array
     */
    public function getOfferIdList()
    {
        $offerIdList = array();
        foreach ($this->getOfferList() as $offer)
            $offerIdList[] = $offer->getEmployeeId();
        
        return $offerIdList;
    }

    /**
     * Gets the id list of the current employees of the team
     * 
     * @return array
     */
    public function getEmployeeIdList()
    {
        $employeeIdList = array();
        foreach ($this->getContractList() as $contract)
            $employeeIdList[] = $contract->getEmployeeId();
        
        return $employeeIdList;
    }

    /**
     * Gets the list of the coaches of the team sorted by position and lineup
     * 
     * @return array of YBCore_Model_User_Coach
     */
    public function getCoachList()
    {
        $coachList = array();
        foreach ($this->getSortedRoster() as $user)
            if ($user instanceof YBCore_Model_User_Coach)
                $coachList[] = $user;
        
        return $coachList;
    }

    /**
     * Gets the list of the players of the team sorted by position and lineup
     * 
     * @return array of YBCore_Model_User_Player
     */
    public function getPlayerList()
    {
        $playerList = array();
        foreach ($this->getSortedRoster() as $user)
            if ($user instanceof YBCore_Model_User_Player)
                $playerList[] = $user;
        
        return $playerList;
    }

    /**
     * Sets current all connections (contract,offer,request) of the team.
     *
     * @param array of YBCore_Model_Connection_Abstract $connectionList 
     */
    private function _setConnectionList(array $connectionList)
    {
        $this->_connectionList = $connectionList;
    }

    /**
     * Returns current connections (contract,offer,request) of the team.
     * Sets the days of the connections
     * 
     * All complexity is to reduce db queries, sorry
     * 
     * @return array of YBCore_Model_Connection_Abstract
     */
    private function _getConnectionList()
    {
        if (null === $this->_connectionList)
        {
            $connectionList = $this->_getMapper()->loadConnectionList($this->getId());
            
            // load days if there are any connections
            if (false === empty($connectionList))
            {
                // array to hold connection and ids which may have day (HC contract and offers)
                $filteredConnectionIdList = array();
                $filteredConnectionList = array();
                // type of connections which may have day
                $connectionTypeList = array(YBCore_Model_Connection_Mapper_Contract::STATUS, YBCore_Model_Connection_Mapper_Offer::STATUS);
                foreach ($connectionList as $connection)
                    if (true === in_array($connection->getStatus(), $connectionTypeList, true))
                    {
                        $filteredConnectionIdList[] = $connection->getId();
                        $filteredConnectionList[] = $connection;
                    }
                
                if (false === empty($filteredConnectionIdList))
                {
                    $connectionDayMapper = YBCore_Mapper_Factory::getMapper('YBCore_Model_Connection_Day');
                    $connectionDayList = $connectionDayMapper->loadByConnectionId($filteredConnectionIdList);
                    
                    if (false === empty($connectionDayList))
                    {
                        // connections which have a day (HC contract of offer(s))
                        $groupedConnectionDayList = array();
                        foreach ($connectionDayList as $connectionDay)
                            $groupedConnectionDayList[$connectionDay->getConnectionId()][] = $connectionDay;
                        
                        $headCoachContractDayList = array();
                        foreach ($filteredConnectionList as $connection)
                            if (in_array($connection->getId(), array_keys($groupedConnectionDayList)))
                            {
                                switch ($connection->getStatus())
                                {
                                    case YBCore_Model_Connection_Mapper_Contract::STATUS:
                                        $headCoachContractDayList = $groupedConnectionDayList[$connection->getId()];
                                        break;
                                    case YBCore_Model_Connection_Mapper_Offer::STATUS:
                                        $connection->setDayList($groupedConnectionDayList[$connection->getId()]);
                                        break;
                                }
                            }
                        
     // HC contract days applies to all players' contracts and offers
                        foreach ($filteredConnectionList as $connection)
                        {
                            $tmpconnectionDayList = $connection->getDayList();
                            if (true === empty($tmpconnectionDayList))
                                $connection->setDayList($headCoachContractDayList);
                        }
                    }
                }
            }
            $this->_setConnectionList($connectionList);
        }
        return $this->_connectionList;
    }

    /**
     * Returns the connection whose id is given
     * Verifies that given connection Id belongs to one of this team's connections 
     * and returns that connection, if not verified throws YBCore_Exception_Authentication
     *
     * @throws YBCore_Exception_Authentication
     * @param int|string $connectionId
     * @return YBCore_Model_Connection_Abstract
     */
    public function verifyConnection($connectionId)
    {
        foreach ($this->_getConnectionList() as $connection)
            if ($connection->getId() === (int) $connectionId)
                return $connection;
        
        throw new YBCore_Exception_Authentication();
    }

    /**
     * Returns the game whose id is given
     * Verifies that given given Id exists and has the given status 
     * if verified returns that game, if not verified throws YBCore_Exception_Authentication
     *
     * @throws YBCore_Exception_Authentication
     * @param int $connectionId
     * @param int $status YBCore_Model_Event_Game_Mapper_*::STATUS
     * @return YBCore_Model_Event_Game_Abstract
     */
    public function verifyGame($gameId, $status)
    {
        $gameMapper = YBCore_Mapper_Factory::getMapper('YBCore_Model_Event_Game_Abstract');
        $gameList = $gameMapper->loadGameById($gameId, $status);
        
        if (true === empty($gameList))
            throw new YBCore_Exception_Authentication();
        
        return $gameList[0];
    }

    /**
     * Returns connection list by status.
     *
     * @param int $status YBCore_Model_Connection_Mapper_*::STATUS
     * @return array of YBCore_Model_Connection_Abstract
     */
    public function getConnectionListByStatus($status)
    {
        switch ($status)
        {
            case YBCore_Model_Connection_Mapper_Contract::STATUS:
                return $this->getContractList();
            case YBCore_Model_Connection_Mapper_Offer::STATUS:
                return $this->getOfferList();
            case YBCore_Model_Connection_Mapper_Request::STATUS:
                return array_merge($this->getTransferRequestList(), $this->getDismissRequestList());
        }
    }

    /**
     * Sets current contracts of the team.
     *
     * @param array of YBCore_Model_Connection_Contract $contractList 
     */
    private function _setContractList(array $contractList)
    {
        $this->_contractList = $contractList;
    }

    /**
     * Returns current contracts of the team.
     *
     * @return array of YBCore_Model_Connection_Contract
     */
    public function getContractList()
    {
        if (null === $this->_contractList)
        {
            $contractList = $this->_loadConnectionListByStatus(YBCore_Model_Connection_Mapper_Contract::STATUS);
            $this->_setContractList($contractList);
        }
        return $this->_contractList;
    }

    /**
     * Returns contract of the given employee
     *
     * @param int|YBCore_Interface_Employee $employee
     * @return YBCore_Model_Connection_Contract
     */
    public function getContractOfEmployee($employee)
    {
        $employeeId = null;
        if ($employee instanceof YBCore_Interface_Employee)
            $employeeId = $employee->getId();
        elseif (is_numeric($employee))
            $employeeId = (int) $employee;
        
        foreach ($this->getContractList() as $contract)
            if ($contract->getEmployeeId() === $employeeId)
                return $contract;
    }

    /**
     * Returns a bool to indicate if the contract given, 
     * is an expense or income for this model 
     * 
     * @param YBCore_Model_Connection_Contract $contract
     * @return bool
     */
    public function isExpense($contract)
    {
        return true;
    }

    /**
     * Sets current offers of the team.
     *
     * @param array of YBCore_Model_Connection_Offer $offerList 
     */
    private function _setOfferList(array $offerList)
    {
        $this->_offerList = $offerList;
    }

    /**
     * Returns current offers of the team.
     *
     * @return array of YBCore_Model_Connection_Offer
     */
    public function getOfferList()
    {
        if (null === $this->_offerList)
        {
            $offerList = $this->_loadConnectionListByStatus(YBCore_Model_Connection_Mapper_Offer::STATUS);
            $this->_setOfferList($offerList);
        }
        return $this->_offerList;
    }

    /**
     * Sets current transfer requests of the team.
     *
     * @param array of YBCore_Model_Connection_Request $transferRequestList 
     */
    private function _setTransferRequestList(array $transferRequestList)
    {
        $this->_transferRequestList = $transferRequestList;
    }

    /**
     * Returns current transfer requests of the team.
     * @todo double foreach loop! not the best way
     *
     * @return array of YBCore_Model_Connection_Request
     */
    public function getTransferRequestList()
    {
        if (null === $this->_transferRequestList)
        {
            $requestList = $this->_loadConnectionListByStatus(YBCore_Model_Connection_Mapper_Request::STATUS);
            
            $transferRequestList = array();
            foreach ($requestList as $request)
                if (false === in_array($request->getEmployeeId(), $this->getEmployeeIdList(), true))
                    $transferRequestList[] = $request;
            
            $this->_setTransferRequestList($transferRequestList);
        }
        return $this->_transferRequestList;
    }

    /**
     * Sets current dismiss requests of the team.
     *
     * @param array of YBCore_Model_Connection_Request $dismissRequestList 
     */
    private function _setDismissRequestList(array $dismissRequestList)
    {
        $this->_dismissRequestList = $dismissRequestList;
    }

    /**
     * Returns current dismiss requests of the team.
     * @todo double foreach loop! not the best way
     *
     * @return array of YBCore_Model_Connection_Request
     */
    public function getDismissRequestList()
    {
        if (null === $this->_dismissRequestList)
        {
            $requestList = $this->_loadConnectionListByStatus(YBCore_Model_Connection_Mapper_Request::STATUS);
            
            $dismissRequestList = array();
            foreach ($requestList as $request)
                if (true === in_array($request->getEmployeeId(), $this->getEmployeeIdList(), true))
                    $dismissRequestList[] = $request;
            
            $this->_setDismissRequestList($dismissRequestList);
        }
        return $this->_dismissRequestList;
    }

    /** 
     * Returns the connections with the given status(es)
     *
     * @param int|array $status
     * @return array of YBCore_Model_Connection_Abstract
     */
    private function _loadConnectionListByStatus($status)
    {
        $connectionList = array();
        foreach ($this->_getConnectionList() as $connection)
            if (in_array($connection->getStatus(), (array) $status))
                $connectionList[] = $connection;
        return $connectionList;
    }

    /**
     * Clears the connections of the team according to given status.
     *
     * @param [optional] YBCore_Model_Connection_Mapper_*::STATUS $status = null
     */
    private function _clearConnectionList($status = null)
    {
        $this->_connectionList = null;
        
        switch ($status)
        {
            case YBCore_Model_Connection_Mapper_Contract::STATUS:
                $this->_contractList = null;
                break;
            case YBCore_Model_Connection_Mapper_Offer::STATUS:
                $this->_offerList = null;
                break;
            case YBCore_Model_Connection_Mapper_Request::STATUS:
                $this->_transferRequestList = null;
                $this->_dismissRequestList = null;
                break;
            default:
                $this->_contractList = null;
                $this->_offerList = null;
                $this->_transferRequestList = null;
                $this->_dismissRequestList = null;
                break;
        }
    }

    /**
     * Saves the given connection.
     * 
     * @param YBCore_Model_Connection_Abstract $connection
     */
    public function saveSecureConnection(YBCore_Model_Connection_Abstract $connection)
    {
        $connection->save();
        if ($connection->getEmployee() instanceof YBCore_Model_User_Coach && YBCore_Model_Connection_Mapper_Offer::STATUS === $connection->getStatus())
            $connection->saveDayList();
        $this->_clearConnectionList($connection->getStatus());
    }

    /**
     * Removes the given connection.
     *
     * @param YBCore_Model_Connection_Abstract $connection
     */
    public function removeSecureConnection(YBCore_Model_Connection_Abstract $connection)
    {
        if ($connection->getEmployee() instanceof YBCore_Model_User_Coach && YBCore_Model_Connection_Mapper_Request::STATUS !== $connection->getStatus())
            $connection->deleteDayList();
        $connection->delete();
        $this->_clearConnectionList($connection->getStatus());
    }

    /**
     * Saves the given game.
     * 
     * @param YBCore_Model_Event_Game_Abstract $game
     */
    public function saveSecureGame(YBCore_Model_Event_Game_Abstract $game)
    {
        $game->save();
        
        // clear data in schedule object
        $schedule = $this->getSchedule();
        $schedule->clearGameList(YBCore_Model_Event_Game_Mapper_Challenge::STATUS);
        $schedule->clearGameList($game->getStatus());
    }

    /**
     * Removes the given game.
     *
     * @param YBCore_Model_Event_Game_Abstract $game
     */
    public function removeSecureGame(YBCore_Model_Event_Game_Abstract $game)
    {
        $game->delete();
        
        // clear data in schedule object
        $schedule = $this->getSchedule();
        $schedule->clearGameList($game->getStatus());
    }

    /**
     * Gets the $_schedule.
     *
     * @return YBCore_Model_Schedule_Team
     */
    public function getSchedule()
    {
        if (null === $this->_schedule)
        {
            $scheduleClassName = $this->_getScheduleClassName();
            $this->_schedule = new $scheduleClassName($this);
        }
        return $this->_schedule;
    }

    /**
     * Gets the ScheduleClassName of the user.
     *
     * @return string
     */
    private function _getScheduleClassName()
    {
        $modelClassName = get_class($this);
        
        $tmpArr = explode("_", $modelClassName);
        $tmpArr[] = end($tmpArr);
        $tmpArr[count($tmpArr) - 2] = "Schedule";
        return implode("_", $tmpArr);
    }
}