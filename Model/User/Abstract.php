<?php
/**
 * Abstract class for building for user classes.
 */
abstract class YBCore_Model_User_Abstract extends YBCore_Model_Abstract
{

    /**
     * id of the user
     *
     * @var int
     */
    private $_id;

    /**
     * facebook id of the user
     *
     * @var int
     */
    private $_fbId;

    /**
     * type of the the user (HC,TO,Player)
     *
     * @var int YBCore_Model_User_Mapper_User::USERTYPE_* 
     */
    private $_type;

    /**
     * position of the the user 
     * for player (C,F,G)
     * for coach (HC, AC)
     *
     * @var int
     */
    private $_position;

    /**
     * Line up order of the the user in team roster 
     *
     * @var int
     */
    private $_lineUp;

    /**
     * Account balance of the user, dear good old veripark(http://www.veripark.com/) 
     *
     * @var int
     */
    private $_balance;

    /**
     * Data fetched from fb 
     *
     * @var string
     */
    private $_fbName;

    private $_fbFirstName;

    private $_fbMiddleName;

    private $_fbLastName;

    private $_fbLink;

    /**
     * id of the current team of the user  
     *
     * @var int
     */
    protected $_teamId;

    /**
     * current team of the user  
     *
     * @var YBCore_Model_Team
     */
    protected $_team;

    /**
     * schedule object of the user
     *
     * @var YBCore_Model_Schedule_Abstract
     */
    private $_schedule;

    /**
     * list of connections (contract, offer, request) of the user
     *
     * @var array of YBCore_Model_Connection_Abstract
     */
    protected $_connectionList;

    /**
     * list of current contracts of the user
     *
     * @var array of YBCore_Model_Connection_Contract
     */
    protected $_contractList;

    /**
     * list of current offers of the user
     *
     * @var array of YBCore_Model_Connection_Offer
     */
    protected $_offerList;

    /**
     * list of current transfer requests of the user
     *
     * @var array of YBCore_Model_Connection_Request
     */
    private $_transferRequestList;

    /**
     * list of current dismiss requests of the user
     *
     * @var array of YBCore_Model_Connection_Request
     */
    private $_dismissRequestList;

    /**
     * @param [optional] int $id = null
     */
    public function __construct($id = null)
    {
        $type = $this->_getMapperConstant('TYPE');
        $this->setType($type);
        
        if (null !== $id)
            $this->_load((int) $id);
    }

    /**
     * Sets connections of the user.
     *
     * @param array of YBCore_Model_Connection_Abstract $connectionList 
     */
    private function _setConnectionList(array $connectionList)
    {
        $this->_connectionList = $connectionList;
    }

    /**
     * Returns connections of the user.
     *
     * @return array of YBCore_Model_Connection_Abstract
     */
    protected function _getConnectionList()
    {
        if ($this->_connectionList === null)
        {
            $connectionList = $this->_getMapper()->loadConnectionList($this->getId());
            
            // load connection days if there are any connections
            if (false === empty($connectionList))
            {
                $connectionListIdArray = array();
                foreach ($connectionList as $connection)
                    $connectionListIdArray[] = $connection->getId();
                
                $connectionDayMapper = YBCore_Mapper_Factory::getMapper('YBCore_Model_Connection_Day');
                $connectionDayList = $connectionDayMapper->loadByConnectionId($connectionListIdArray);
                
                foreach ($connectionList as $connection)
                {
                    $currentDayList = array();
                    if (YBCore_Model_Connection_Mapper_Connection::TYPE_TEAM == $connection->getType() && $this instanceof YBCore_Model_User_Player)
                    {
                        $teamId = $connection->getEmployerId();
                        if (YBCore_Model_Connection_Mapper_Contract::STATUS === $connection->getStatus())
                            $this->setTeamId($teamId);
                        
     // if a team contract/offer fetch days from teams contracts (if any)
                        $employerTeam = new YBCore_Model_Team();
                        $employerTeam->setId($teamId);
                        $employerTeamContractList = $employerTeam->getContractList();
                        if (false === empty($employerTeamContractList))
                            $currentDayList = $employerTeamContractList[0]->getDayList();
                    } else
                    {
                        foreach ($connectionDayList as $connectionDay)
                            if ($connection->getId() === $connectionDay->getConnectionId())
                                $currentDayList[] = $connectionDay;
                    }
                    
                    $connection->setDayList($currentDayList);
                }
            }
            $this->_setConnectionList($connectionList);
        }
        return $this->_connectionList;
    }

    /**
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
        
        throw new YBCore_Exception_Authentication();
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
     * Sets current contracts of the user.
     *
     * @param array of YBCore_Model_Connection_Contract $contractList 
     */
    private function _setContractList(array $contractList)
    {
        $this->_contractList = $contractList;
    }

    /**
     * Returns current contracts of the user.
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
     * Sets current offers of the user.
     *
     * @param array of YBCore_Model_Connection_Offer $offerList 
     */
    private function _setOfferList(array $offerList)
    {
        $this->_offerList = $offerList;
    }

    /**
     * Returns current offers of the user.
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
     * Sets current transfer requests of the user.
     *
     * @param array of YBCore_Model_Connection_Request $transferRequestList 
     */
    private function _setTransferRequestList(array $transferRequestList)
    {
        $this->_transferRequestList = $transferRequestList;
    }

    /**
     * Returns current transfer requests of the user.
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
                if ($this->getTeamId() !== $request->getEmployerId())
                    $transferRequestList[] = $request;
            
            $this->_setTransferRequestList($transferRequestList);
        }
        return $this->_transferRequestList;
    }

    /**
     * Sets current dismiss requests of the user.
     *
     * @param array of YBCore_Model_Connection_Request $dismissRequestList 
     */
    private function _setDismissRequestList(array $dismissRequestList)
    {
        $this->_dismissRequestList = $dismissRequestList;
    }

    /**
     * Returns current dismiss requests of the user.
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
                if ($this->getTeamId() === $request->getEmployerId())
                    $dismissRequestList[] = $request;
            
            $this->_setDismissRequestList($dismissRequestList);
        }
        return $this->_dismissRequestList;
    }

    /**
     * Returns individual contracts of the user.
     *
     * @return array of YBCore_Model_Connection_Contract
     */
    protected function _getIndividualContractList()
    {
        $individualContractList = array();
        foreach ($this->getContractList() as $contract)
            if ($contract->getType() == YBCore_Model_Connection_Mapper_Connection::TYPE_INDIVIDUAL)
                $individualContractList[] = $contract;
        
        return $individualContractList;
    }

    /**
     * Loads the connections with the given status.
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
     * Clears the connections of the user according to given status.
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
     * Creates the user whose id is given.
     * 
     * @throws YBCore_Exception_Authentication
     * @param int $targetUserId
     * @return YBCore_Model_User_Abstract
     */
    protected function _getUser($targetUserId)
    {
        $userMapper = YBCore_Mapper_Factory::getMapper('YBCore_Model_User_Abstract');
        $targetUser = $userMapper->initializeUser($targetUserId);
        if (false === empty($targetUser))
            return $targetUser = $targetUser[0];
        
        throw new YBCore_Exception_Authentication();
    }

    /**
     * Adds the connection whose id is given.
     * 
     * @throws YBCore_Exception_Authentication
     * @throws YBCore_Exception_Logic
     * @param int $connectionId
     */
    public function modifyConnection($connectionId)
    {
        // verify connection
        $connection = $this->_verifyConnection($connectionId);
        $this->_modifySecureConnection($connection);
    }

    /**
     * Adds the given connection.
     * 
     * @param YBCore_Model_Connection_Abstract $connection
     */
    protected function _saveSecureConnection($connection)
    {
        $connection->save();
        // if coach offer save days
        if ($connection->getEmployee() instanceof YBCore_Model_User_Coach && YBCore_Model_Connection_Mapper_Offer::STATUS === $connection->getStatus())
            $connection->saveDayList();
        $this->_clearConnectionList($connection->getStatus());
    }

    /**
     * Removes the connection whose id is given.
     * 
     * @throws YBCore_Exception_Authentication
     * @param int $connectionId
     */
    public function removeConnection($connectionId)
    {
        // verify connection
        $connection = $this->_verifyConnection($connectionId);
        $this->_removeSecureConnection($connection);
    }

    /**
     * Removes the given connection.
     *
     * @param YBCore_Model_Connection_Abstract $connection
     */
    protected function _removeSecureConnection($connection)
    {
        $connection->delete();
        
        if (YBCore_Model_Connection_Mapper_Connection::TYPE_TEAM === $connection->getType() && YBCore_Model_Connection_Mapper_Contract::STATUS === $connection->getStatus())
            $this->_clearTeam();
        $this->_clearConnectionList($connection->getStatus());
    }

    /**
     * Accepts the given challenge.
     * This method allowed only for TO, overridden in TO
     *
     * @todo rename to modify like
     * 
     * @param int $gameId
     */
    public function acceptChallenge($gameId)
    {
        throw new YBCore_Exception_Authentication();
    }

    /**
     * Withdraws the given challenge.
     * This method allowed only for TO, overridden in TO
     *
     * @todo rename to remove  like
     *
     * @param int $gameId
     */
    public function withdrawChallenge($gameId)
    {
        throw new YBCore_Exception_Authentication();
    }

    /**
     * Creates a public challenge for user's team
     * This method allowed only for TO, overridden in TO
     */
    public function createChallenge()
    {
        throw new YBCore_Exception_Authentication();
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
     * Sets the $_fbId.
     *
     * @param int $fbId
     */
    public function setFbId($fbId)
    {
        $this->_fbId = (int) $fbId;
    }

    /**
     * Gets the $_fbId.
     *
     * @return int
     */
    public function getFbId()
    {
        return $this->_fbId;
    }

    /**
     * Sets the $_type.
     *
     * @param int $type 
     */
    public function setType($type)
    {
        $this->_type = (int) $type;
    }

    /**
     * Gets the $_type.
     *
     * @return int
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Sets the $_position.
     *
     * @param int $position 
     */
    public function setPosition($position)
    {
        $this->_position = (int) $position;
    }

    /**
     * Gets the $_position.
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->_position;
    }

    /**
     * Sets the $_lineUp.
     *
     * @param int $lineUp 
     */
    public function setLineUp($lineUp)
    {
        $this->_lineUp = (int) $lineUp;
    }

    /**
     * Gets the $_lineUp.
     *
     * @return int
     */
    public function getLineUp()
    {
        return $this->_lineUp;
    }

    /**
     * Sets the $_balance.
     *
     * @param int $balance 
     */
    public function setBalance($balance)
    {
        $this->_balance = (int) $balance;
    }

    /**
     * Gets the $_balance.
     *
     * @return int
     */
    public function getBalance()
    {
        return $this->_balance;
    }

    /**
     * Sets the $_fbName.
     *
     * @param string $fbName
     * @return void
     */
    public function setFbName($fbName)
    {
        $this->_fbName = $fbName;
    }

    /**
     * Gets the $_fbName.
     *
     * @param [optional] string $type = "name", defaults to full name
     * @return string
     */
    public function getFbName($type = "name")
    {
        switch ($type)
        {
            case "name":
                return $this->_fbName;
            case "first_name":
                return $this->getFbFirstName();
            case "last_name":
                return $this->getFbLastName();
        }
    }

    /**
     * Sets the $_fbFirstName.
     *
     * @param string $fbFirstName
     * @return void
     */
    public function setFbFirstName($fbFirstName)
    {
        $this->_fbFirstName = $fbFirstName;
    }

    /**
     * Gets the $_fbFirstName.
     *
     * @return string
     */
    public function getFbFirstName()
    {
        return $this->_fbFirstName;
    }

    /**
     * Sets the $_fbMiddleName.
     *
     * @param string $fbMiddleName
     * @return void
     */
    public function setFbMiddleName($fbMiddleName)
    {
        $this->_fbMiddleName = $fbMiddleName;
    }

    /**
     * Gets the $_fbMiddleName.
     *
     * @return string
     */
    public function getFbMiddleName()
    {
        return $this->_fbMiddleName;
    }

    /**
     * Sets the $_fbLastName.
     *
     * @param string $fbLastName
     * @return void
     */
    public function setFbLastName($fbLastName)
    {
        $this->_fbLastName = $fbLastName;
    }

    /**
     * Gets the $_fbLastName.
     *
     * @return string
     */
    public function getFbLastName()
    {
        return $this->_fbLastName;
    }

    /**
     * Sets the $_fbLink.
     *
     * @param string $fbLink
     * @return void
     */
    public function setFbLink($fbLink)
    {
        $this->_fbLink = $fbLink;
    }

    /**
     * Gets the $_fbLink.
     *
     * @return string
     */
    public function getFbLink()
    {
        return $this->_fbLink;
    }

    /**
     * Sets the $_teamId.
     *
     * @param int 
     */
    public function setTeamId($teamId)
    {
        $this->_clearTeam();
        $this->_teamId = (int) $teamId;
    }

    /**
     * Gets the $_teamId.
     *
     * @return int
     */
    public function getTeamId()
    {
        $teamContract = $this->getTeamContract();
        if (null === $this->_teamId && null !== $teamContract)
        {
            $teamId = $teamContract->getEmployerId();
            $this->setTeamId($teamId);
        }
        return $this->_teamId;
    }

    /**
     * Gets the team contract of the user.
     *
     * @return YBCore_Model_Connection_Contract
     */
    public function getTeamContract()
    {
        foreach ($this->getContractList() as $contract)
            if ($contract->getType() == YBCore_Model_Connection_Mapper_Connection::TYPE_TEAM)
                return $contract;
    }

    /**
     * Sets the $_team.
     *
     * @param YBCore_Model_Team $team 
     */
    protected function _setTeam(YBCore_Model_Team $team)
    {
        $this->_team = $team;
        $this->_teamId = $team->getId();
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
            $teamId = $this->getTeamId();
            if (null !== $teamId)
            {
                $team = new YBCore_Model_Team($teamId);
                $this->_setTeam($team);
            }
        }
        return $this->_team;
    }

    /**
     * Clears the $_team.
     *
     */
    private function _clearTeam()
    {
        $this->_teamId = null;
        $this->_team = null;
    }

    /**
     * Gets the $_schedule.
     *
     * @return YBCore_Model_Schedule_Abstract
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