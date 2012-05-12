<?php
/**
 * Factory class for mapper instances.
 */
class YBCore_Mapper_Factory extends YBCore_Abstract
{

    /**
     * array for mapper instances
     *
     * @var array of YBCore_Mapper_Abstract
     */
    private static $_mapperInstanceList = array();

    /**
     * returns the mapper instance for the given model
     *
     * @param YBCore_Model_Abstract|string $model
     * @return YBCore_Mapper_Abstract
     */
    public static function getMapper($model)
    {
        $modelClassName = $model instanceof YBCore_Model_Abstract ? get_class($model) : (string) $model;
        
        if (!isset(self::$_mapperInstanceList[$modelClassName]))
        {
            $mapperClassName = self::_getMapperClassName($modelClassName);
            $mapperInstance = new $mapperClassName();
            $mapperInstance->setModelClassName($modelClassName);
            self::$_mapperInstanceList[$modelClassName] = $mapperInstance;
        }
        return self::$_mapperInstanceList[$modelClassName];
    }

    /**
     * returns the mapper class name for the given model
     *
     * @param $modelClassName string
     * @return string
     */
    private static function _getMapperClassName($modelClassName)
    {
        $pathList = explode("_", $modelClassName);
        
        $fileName = array_pop($pathList);
        array_push($pathList, "Mapper");
        
        // special handling for abstract classes
        if ('Abstract' === $fileName)
            $fileName = $pathList[count($pathList) - 2];
        
        array_push($pathList, $fileName);
        return implode("_", $pathList);
    }
}