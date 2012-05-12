<?php
/**
 * Utility class for string formatting
 * Declared abstract to prevent instances, all methods should be static 
 */
abstract class YBCore_Utility_StringFormatter
{

    /**
     * Array of day names
     * @todo get rid of this array use php date('l')
     */
    public static $dayNameList = array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday");

    /**
     * Retrieves the name of the given day
     * 
     * @param int $id
     */
    public static function getDayName($id)
    {
        return self::$dayNameList[(int) $id];
    }
}