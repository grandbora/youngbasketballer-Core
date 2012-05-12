<?php
/**
 * Utility class for DateTime functions
 * Declared abstract to prevent instances, all methods should be static 
 */
abstract class YBCore_Utility_DateTime
{
    const DAY_SECONDS = 86400; // 24 * 60 * 60

    
    /**
     * Game Time, is set in bootstrap
     * @todo is it better to read from config directly?
     */
    public static $gameTime;

    /**
     * Training Time, is set in bootstrap
     * @todo is it better to read from config directly?
     */
    public static $trainingTime;

    /**
     * Returns if the gametime passed already or not 
     * 
     * @return booolean
     */
    public static function hasGameTimePassed()
    {
        return time() >= strtotime(self::$gameTime);
    }

    /**
     * Returns the next game datetime 
     *
     * @return string
     */
    public static function getNextGameDateTime()
    {
        if (YBCore_Utility_DateTime::hasGameTimePassed())
            return date('Y-m-d H:i:s', strtotime('tomorrow ' . self::$gameTime));
        else
            return date('Y-m-d H:i:s', strtotime('today ' . self::$gameTime));
    }

    /**
     * Returns the next training datetime 
     *
     * @param int $dayId
     * @return string
     */
    public static function getNextTrainingDateTime($dayId)
    {
        $dayName = YBCore_Utility_StringFormatter::getDayName($dayId);
        return date('Y-m-d H:i:s', strtotime($dayName . ' ' . self::$trainingTime));
    }

    /**
     * Returns current day Id (today's Id) 
     * N : ISO-8601 numeric representation of the day of the week :1 (for Monday) through 7 (for Sunday)
     * in YBCore day id starts from zero(0) therefore substract 1
     * @return int
     */
    public static function getCurrentDayId()
    {
        return date('N') - 1;
    }

    /**
     * Returns a date formatted by $format and calculated by adding $dayCount times day to time() 
     * @return string formatted date
     */
    public static function calculateDate($dayCount, $format)
    {
        return date($format, time() + $dayCount * self::DAY_SECONDS);
    }
}