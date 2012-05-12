<?php
/**
 * Map class to store mapping information of the models.
 */
class YBCore_Mapper_Map extends YBCore_Abstract
{
    
    /**
     * Getter method name prefix
     *
     * @var string
     */
    const PREFIX_GETTER = "get";
    
    /**
     * Setter method name prefix
     *
     * @var string
     */
    const PREFIX_SETTER = "set";
    
    /**
     * Suffix for object type
     *
     * @var string
     */
    const SUFFIX_OBJECT = "Id";

    /**
     * Model member name
     *
     * @var string
     */
    private $_modelMemberName;

    /**
     * Entity column name
     * 
     * @var string
     */
    private $_entityColumnName;

    /**
     * Data type
     * 
     * @var string
     */
    private $_dataType;

    /**
     * @param string $modelMemberName
     * @param string $entityColumnName
     * @param [optional] string $dataType = YBCore_Mapper_Abstract::STRING
     */
    public function __construct($modelMemberName, $entityColumnName, $dataType = YBCore_Mapper_Abstract::DATATYPE_STRING)
    {
        $this->_setModelMemberName($modelMemberName);
        $this->_setEntityColumnName($entityColumnName);
        $this->_setDataType($dataType);
    }

    /**
     * Sets the $_modelMemberName.
     *
     * @param $modelMemberName string
     */
    private function _setModelMemberName($modelMemberName)
    {
        $this->_modelMemberName = (string) $modelMemberName;
    }

    /**
     * Gets the $_modelMemberName.
     *
     * @return string
     */
    public function getModelMemberName()
    {
        return $this->_modelMemberName;
    }

    /**
     * Gets the formattedMemberName of the $_modelMemberName.
     *
     * @return string
     */
    private function _getFormattedMemberName()
    {
        return ucfirst($this->getModelMemberName());
    }

    /**
     * Gets the $_modelMemberName getter method name.
     *
     * @return string
     */
    public function getGetterMethodName()
    {
        return self::PREFIX_GETTER . $this->_getFormattedMemberName() . $this->_getMethodNameSuffix();
    }

    /**
     * Gets the $_modelMemberName setter method name.
     *
     * @return string
     */
    public function getSetterMethodName()
    {
        return self::PREFIX_SETTER . $this->_getFormattedMemberName() . $this->_getMethodNameSuffix();
    }

    /**
     * Gets method(getter|setter) name suffix according to datatype
     *
     * @return string
     */
    private function _getMethodNameSuffix()
    {
        $suffix = null;
        if ($this->getDataType() === YBCore_Mapper_Abstract::DATATYPE_OBJECT)
            $suffix = self::SUFFIX_OBJECT;
        
        return $suffix;
    }

    /**
     * Sets the $_entityColumnName.
     *
     * @param $entityColumnName string
     */
    private function _setEntityColumnName($entityColumnName)
    {
        $this->_entityColumnName = (string) $entityColumnName;
    }

    /**
     * Gets the $_entityColumnName.
     *
     * @return string
     */
    public function getEntityColumnName()
    {
        return $this->_entityColumnName;
    }

    /**
     * Sets the $_dataType.
     *
     * @param $dataType string
     */
    private function _setDataType($dataType)
    {
        $this->_dataType = (string) $dataType;
    }

    /**
     * Gets the $_dataType.
     *
     * @return string
     */
    public function getDataType()
    {
        return $this->_dataType;
    }
}