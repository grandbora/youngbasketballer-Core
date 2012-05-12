<?php
/**
 * Player class
 */
class YBCore_Model_User_Player extends YBCore_Model_User_Abstract implements YBCore_Interface_Employee, YBCore_Interface_Employer
{

    /**
     * Returns id list of individual contractors who are offered contract
     *
     * @return array of int
     */
    public function getIndividualOfferIdList()
    {
        $individualOfferIdList = array();
        foreach ($this->getOfferList() as $offer)
            if (YBCore_Model_Connection_Mapper_Connection::TYPE_INDIVIDUAL === $offer->getType())
                $individualOfferIdList[] = $offer->getEmployeeId();
        
        return $individualOfferIdList;
    }

    /**
     * Returns id list of individual contractors of the player.
     * 
     * @todo make an interface for IndividualContractor methods,
     * implement it in player and coach
     * inherit player and coach from a separate subclass of model_user (model_user_connectable?)
     *
     * @return array of int
     */
    public function getIndividualContractorIdList()
    {
        $trainerIdList = array();
        foreach ($this->_getIndividualContractList() as $contract)
            $trainerIdList[] = $contract->getEmployeeId();
        
        return $trainerIdList;
    }

    /**
     * Returns individual contractors of the player.
     *
     * @return array of YBCore_Model_User_Coach
     */
    public function getIndividualContractorList()
    {
        $trainerIdList = $this->getIndividualContractorIdList();
        if (true === empty($trainerIdList))
            return array();
        
        $coachMapper = YBCore_Mapper_Factory::getMapper('YBCore_Model_User_Coach');
        return $coachMapper->loadUserList($trainerIdList);
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
        return $contract->getType() === YBCore_Model_Connection_Mapper_Connection::TYPE_INDIVIDUAL;
    }

    /**
     * Creates a connection to user whose id is given.
     * 
     * Player makes an offer to a coach
     * check if $targetUserId is valid and a coach
     * check if this coach has already employed by this player 
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
        // verify user
        $targetUser = $this->_getUser($targetUserId);
        
        // verify this is a coach and not employed yet
        if (false === $targetUser instanceof YBCore_Model_User_Coach || true === in_array($targetUser->getId(), $this->getIndividualContractorIdList(), true))
            throw new YBCore_Exception_Logic();
        
     // check if targetUser has already been offered a contract
        if (true === in_array($targetUser->getId(), $this->getIndividualOfferIdList(), true))
            throw new YBCore_Exception_Logic();
        
        $newContract = new YBCore_Model_Connection_Offer();
        $newContract->setEmployerId($this->getId());
        $newContract->setEmployeeId($targetUser->getId());
        $newContract->setSalary($salary);
        $newContract->setType(YBCore_Model_Connection_Mapper_Offer::TYPE_INDIVIDUAL);
        
        // set day list if target user is coach
        $dayModelList = array();
        foreach ($dayList as $dayIndex)
        {
            $day = new YBCore_Model_Connection_Day();
            $day->setDayId($dayIndex);
            $dayModelList[] = $day;
        }
        $newContract->setDayList($dayModelList);
        
        $this->_saveSecureConnection($newContract);
    }

    /**
     * Modifies the given connection.
     * Player accepts team offer
     * 
     * @throws YBCore_Exception_Logic
     * @param YBCore_Model_Connection_Abstract $connection
     */
    protected function _modifySecureConnection($connection)
    {
        if (YBCore_Model_Connection_Mapper_Offer::STATUS !== $connection->getStatus() || YBCore_Model_Connection_Mapper_Connection::TYPE_TEAM !== $connection->getType())
            throw new YBCore_Exception_Logic();
        
        $this->_acceptTeamOffer($connection);
        $this->_saveSecureConnection($connection);
    }

    /**
     * Updates the the given team offer as new team contract
     * Drops current team contract, drops dismiss requests
     * Drops the transfer requests of new team, for this player
     * 
     * @param YBCore_Model_Connection_Offer $connection
     */
    private function _acceptTeamOffer($connection)
    {
        // save value to variable to preventa new db request, 
        $transferRequestList = $this->getTransferRequestList();
        
        // delete former team (if any)
        if (null !== $this->getTeamContract())
            $this->_removeSecureConnection($this->getTeamContract());
        
     // delete new team's transfer requests for this player(if any)
        foreach ($transferRequestList as $transferRequest)
            if ($connection->getEmployerId() === $transferRequest->getEmployerId())
                $this->_removeSecureConnection($transferRequest);
        
     // update accepted offer
        $connection->setStatus(YBCore_Model_Connection_Mapper_Contract::STATUS);
    }

    /**
     * Removes the given connection.
     * Player cancels a contract (team|individual)
     * on team contract cancel, all dismiss requests are dropped
     * Player cancels an offer (team|individual) 
     * refuse team offer OR withdraw individual offer
     * 
     * @param YBCore_Model_Connection_Abstract $connection
     */
    protected function _removeSecureConnection($connection)
    {
        switch ($connection->getType())
        {
            case YBCore_Model_Connection_Mapper_Connection::TYPE_TEAM:
                
                // drop dismiss reqs for this player, if team contract
                if (YBCore_Model_Connection_Mapper_Contract::STATUS === $connection->getStatus())
                    foreach ($this->getDismissRequestList() as $request)
                        parent::_removeSecureConnection($request);
                break;
            
            // remove days if individual connection
            case YBCore_Model_Connection_Mapper_Connection::TYPE_INDIVIDUAL:
                $connection->deleteDayList();
                break;
        }
        
        parent::_removeSecureConnection($connection);
    }
}