<?php
/**
 * Interface for models which can be connected to each other through connection model
 * Every connectable has to impelement schedule
 * 
 * @todo add other connectable necessary functions here (getcontract etc.)
 */
interface YBCore_Interface_Connectable extends YBCore_Interface_Schedule
{

    /**
     * Connection list by status must be available
     * 
     * @param int $status YBCore_Model_Connection_Mapper_*::STATUS
     * @return array of YBCore_Model_Connection_Abstract
     */
    public function getConnectionListByStatus($status);

    /**
     * Returns a bool to indicate if the contract given, 
     * is an expense or income for this model 
     * 
     * @param YBCore_Model_Connection_Contract $contract
     * @return bool
     */
    public function isExpense($contract);

}