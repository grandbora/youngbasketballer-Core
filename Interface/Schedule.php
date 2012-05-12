<?php
/**
 * Interface for models which has a its own schedule
 * 
 */
interface YBCore_Interface_Schedule
{
    /**
     * returns models schedule object 
     */
    public function getSchedule();
}