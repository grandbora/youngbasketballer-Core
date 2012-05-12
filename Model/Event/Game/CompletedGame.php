<?php
/**
 * 
 */
class YBCore_Model_Event_Game_CompletedGame extends YBCore_Model_Event_Game_Abstract
{
    /**
     * Deletes the object data from db 
     * Completed games are not deleted
     * @throws YBCore_Exception_Unbelievable
     */
    public function delete()
    {
        throw new YBCore_Exception_Unbelievable();
    }
}