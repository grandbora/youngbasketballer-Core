<?php
/**
 * Abstract class for YBCore Model classes.
 * @todo check which classes are inheriting this, not all of them should inherit
 */
abstract class YBCore_Model_Abstract
{

    /**
     * Loads the object data from db to model
     *
     * @param $id int
     */
    protected function _load($id)
    {
        $this->_getMapper()->load($this, $id);
    }

    /**
     * Deletes the object data from db 
     *
     */
    public function delete()
    {
        $this->_getMapper()->delete($this->getId());
    }

    /**
     * Saves the object data to db 
     *
     */
    public function save()
    {
        $this->_getMapper()->save($this);
    }

    /**
     * Gets the mapper object
     *
     * @return YBCore_Mapper_Abstract
     */
    protected function _getMapper()
    {
        return YBCore_Mapper_Factory::getMapper($this);
    }

    /**
     * Gets the value of a constant in mapper class
     *
     * @param string $constantName
     * @return int|string
     */
    protected function _getMapperConstant($constantName)
    {
        $mapperClass = get_class($this->_getMapper());
        $constantValue = constant($mapperClass . '::' . $constantName);
        return $constantValue;
    }
}