<?php
/**
 * Coach class
 * 
 */
class YBCore_Model_User_Coach extends YBCore_Model_User_Abstract implements YBCore_Interface_Employee
{

    /**
     * Coach's connection also includes his/her team's requests 
     * 
     * Verifies that given connection Id belongs to one of this user's connections 
     * and returns that connection, if not verified throws YBCore_Exception_Authentication
     *
     * @throws YBCore_Exception_Authentication
     * @param int $connectionId
     * @return YBCore_Model_Connection_Abstract
     */
    protected function _verifyConnection($connectionId)
    {
        foreach ($this->_getConnectionList() as $connection)
            if ($connection->getId() === $connectionId)
                return $connection;
        
     // if not found in own connections look in the team's, if found check if it is a request
        $team = $this->getTeam();
        $connection = $team->verifyConnection($connectionId);
        if (YBCore_Model_Connection_Mapper_Request::STATUS !== $connection->getStatus())
            throw new YBCore_Exception_Authentication();
        
        return $connection;
    }

    /**
     * Returns id list of individual contractors of the coach.
     *
     * @return array of int
     */
    public function getIndividualContractorIdList()
    {
        $playerIdList = array();
        foreach ($this->_getIndividualContractList() as $contract)
            $playerIdList[] = $contract->getEmployerId();
        
        return $playerIdList;
    }

    /**
     * Returns individual contractors of the coach.
     *
     * @return array of YBCore_Model_User_Player
     */
    public function getIndividualContractorList()
    {
        $playerIdList = $this->getIndividualContractorIdList();
        if (true === empty($playerIdList))
            return array();
        
        $playerMapper = YBCore_Mapper_Factory::getMapper('YBCore_Model_User_Player');
        return $playerMapper->loadUserList($playerIdList);
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
        return false;
    }

    /**
     * Creates a connection to user whose id is given.
     * 
     * HC makes a request to a player either transfer or dismiss
     * check if $targetUserId is valid and a player
     * check if this coach has a team
     * check if target user has already been requested 
     *
     * @throws YBCore_Exception_Authentication
     * @throws YBCore_Exception_Logic
     * @param int $targetUserId
     */
    public function createConnection($targetUserId)
    {
        // verify user
        $targetUser = $this->_getUser($targetUserId);
        
        // coach must have a team
        if (null === $this->getTeamId())
            throw new YBCore_Exception_Logic();
        
     // target user must be a player
        if (false === $targetUser instanceof YBCore_Model_User_Player)
            throw new YBCore_Exception_Logic();
        
     // target user must not be requested before 
        $team = $this->getTeam();
        if (true === in_array($targetUser->getId(), $team->getRequestIdList()))
            throw new YBCore_Exception_Logic();
        
        $newContract = new YBCore_Model_Connection_Request();
        $newContract->setEmployerId($this->getTeamId());
        $newContract->setEmployeeId($targetUserId);
        $newContract->setType(YBCore_Model_Connection_Mapper_Offer::TYPE_TEAM);
        
        $this->_saveSecureConnection($newContract);
    }

    /**
     * Modifies the given connection.
     * Coach accepts team|individual offer
     * on team offer accept current team contract is dropped
     * on team offer accept all requests that belongs to this coach are dropped
     * 
     * @throws YBCore_Exception_Logic
     * @param YBCore_Model_Connection_Abstract $connection
     */
    protected function _modifySecureConnection($connection)
    {
        if (YBCore_Model_Connection_Mapper_Offer::STATUS !== $connection->getStatus())
            throw new YBCore_Exception_Logic();
        
     // if team offer check if already has a team
        if (YBCore_Model_Connection_Mapper_Connection::TYPE_TEAM == $connection->getType() && null !== $this->getTeamContract())
            $this->_removeSecureConnection($this->getTeamContract());
        
     // update accepted offer
        $connection->setStatus(YBCore_Model_Connection_Mapper_Contract::STATUS);
        
        $this->_saveSecureConnection($connection);
    }

    /**
     * Removes the given connection.
     * Coach cancels a contract (team|individual)
     * on team contract cancel, all requests of this coach are dropped
     * Coach refuses an offer (team|individual) 
     * Coach withdraws a request of self
     * 
     * @todo may need to use a db transaction here
     *
     * @param YBCore_Model_Connection_Abstract $connection
     */
    protected function _removeSecureConnection($connection)
    {
        switch ($connection->getStatus())
        {
            case YBCore_Model_Connection_Mapper_Contract::STATUS:
                
                //remove all the requests of this coach, if team contract is being cancelled
                if (YBCore_Model_Connection_Mapper_Connection::TYPE_TEAM === $connection->getType())
                    foreach ($this->getConnectionListByStatus(YBCore_Model_Connection_Mapper_Request::STATUS) as $request)
                        parent::_removeSecureConnection($request);
            
     // if contract of offer remove days
            case YBCore_Model_Connection_Mapper_Offer::STATUS:
                $connection->deleteDayList();
                break;
        }
        parent::_removeSecureConnection($connection);
    }
}