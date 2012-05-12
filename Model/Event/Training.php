<?php
/**
 * Class for YBCore Model Training.
 */
class YBCore_Model_Event_Training extends YBCore_Model_Event_Abstract
{
    
    /**
     * Training types 
     */
    const TYPE_TEAMTRAINING = 1;
    const TYPE_INDIVIDUALTRAINING = 2;

    /**
     * Contract Id of the training
     *
     * @var int
     */
    private $_contract;

    /**
     * type of the training
     *
     * @var int
     */
    private $_type;

    /**
     * Training event doesnt have a mapper returns null
     *
     * @return null
     */
    protected function _getMapper()
    {
        return null;
    }

    /**
     * @param  YBCore_Model_Connection_Abstract $contract
     * @param  YBCore_Model_Connection_Day $connectionDay  
     */
    public function __construct(YBCore_Model_Connection_Contract $contract, YBCore_Model_Connection_Day $connectionDay)
    {
        $this->_setContract($contract);
        $tmpParticipantList = array();
        switch ($contract->getType())
        {
            case YBCore_Model_Connection_Mapper_Connection::TYPE_TEAM:
                $this->_setType(self::TYPE_TEAMTRAINING);
                $tmpParticipantList[] = $contract->getEmployer(); // team
                break;
            case YBCore_Model_Connection_Mapper_Connection::TYPE_INDIVIDUAL:
                $this->_setType(self::TYPE_INDIVIDUALTRAINING);
                $tmpParticipantList[] = $contract->getEmployer(); // player
                $tmpParticipantList[] = $contract->getEmployee(); // coach
                break;
        }
        $this->_setParticipantList($tmpParticipantList);
        $this->setDay($connectionDay->getDayId());
        
        $nextDate = $this->calculateDate();
        $this->setDate($nextDate);
    }

    /**
     * Gets the time of the game.
     *
     * @return string
     */
    public function getTime()
    {
        return YBCore_Utility_DateTime::$trainingTime;
    }

    /**
     * Sets the $_contract.
     *
     * @param YBCore_Model_Connection_Contract $contract
     */
    private function _setContract(YBCore_Model_Connection_Contract $contract)
    {
        $this->_contract = $contract;
    }

    /**
     * Gets the $_contract.
     *
     * @return YBCore_Model_Connection_Contract
     */
    public function getContract()
    {
        return $this->_contract;
    }

    /**
     * Sets the $_type.
     *
     * @param TYPE_TEAMTRAINING|TYPE_INDIVIDUALTRAINING $type
     */
    private function _setType($type)
    {
        $this->_type = (int) $type;
    }

    /**
     * Gets the $_type.
     *
     * @return TYPE_TEAMTRAINING|TYPE_INDIVIDUALTRAINING
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Calculates the closest date of the event
     *
     * @return string
     */
    public function calculateDate()
    {
        return YBCore_Utility_DateTime::getNextTrainingDateTime($this->getDay());
    }
}