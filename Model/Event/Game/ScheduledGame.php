<?php
/**
 * 
 */
class YBCore_Model_Event_Game_ScheduledGame extends YBCore_Model_Event_Game_Abstract
{

    /**
     * Gets the time of the game.
     *
     * @return string
     */
    public function getTime()
    {
        return YBCore_Utility_DateTime::$gameTime;
    }

    /**
     * Deletes the object data from db 
     * Scheduled games are not deleted
     * @throws YBCore_Exception_Unbelievable
     */
    public function delete()
    {
        throw new YBCore_Exception_Unbelievable();
    }
}