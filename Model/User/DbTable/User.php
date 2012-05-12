<?php
/**
 * DbTable class for user model
 */
class YBCore_Model_User_DbTable_User extends YBCore_DbTable_Abstract
{

    /**
     * Tablename
     * 
     * @var string
     */
    protected $_name = 'user';

    /**
     * Primary Key
     * 
     * @var string
     */
    protected $_primary = 'id';
}