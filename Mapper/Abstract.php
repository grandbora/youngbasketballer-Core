<?php
/**
 * Abstract class for building for mapper classes.
 */
abstract class YBCore_Mapper_Abstract extends YBCore_Abstract
{
    
    /**
     * Defined data types
     *
     * @var string
     */
    const DATATYPE_INT = 'int';
    const DATATYPE_STRING = 'string';
    const DATATYPE_OBJECT = 'object';
    
    /**
     * SQL operators
     * currently supporting only these two operators, if needed more,
     * on empty actions should be implemented where the query is assembled 
     * 
     * @var string
     */
    const WHEREOPERATOR_IN = 'IN';
    const WHEREOPERATOR_NOT_IN = 'NOT IN';
    
    /**
     * SQL Logic operators 
     *
     * @var string
     */
    const LOGICOPERATOR_OR = 'OR';
    const LOGICOPERATOR_AND = 'AND';
    
    /**
     * SQL sort directions 
     *
     * @var string
     */
    const SORTDIRECTION_ASC = 'ASC';
    const SORTDIRECTION_DESC = 'DESC';
    
    /**
     * constant strings for building sql query 
     *
     * @var string
     */
    const STR_WHITESPACE = ' ';
    const STR_LEFTPARENTHESIS = '(';
    const STR_RIGHTPARENTHESIS = ')';
    const STR_FALSE = 'FALSE';
    const STR_TRUE = 'TRUE';

    /**
     * DbTable class name
     *
     * @var string
     */
    private $_dbTableClassName;

    /**
     * Model class name
     * @var string
     */
    private $_modelClassName;

    /**
     * Current Data Row which holds the data of the model
     * @var array of $entityClassName
     */
    private $_currentDataRowSet = array();

    /**
     * Map List, holds the mapping information of the model
     * 
     * @var array of YBCore_Mapper_Map
     */
    private $_mapList;

    /**
     * List of where clauses to be appended to the next sql query
     * 
     * @var array of string 
     */
    private $_whereClauseList = array();

    /**
     * List of order clauses to be appended to the next sql query
     * 
     * @var array of string pairs 
     */
    private $_orderClauseList = array();

    /**
     * Loads the model from db
     * 
     * @param YBCore_Model_Abstract $model 
     * @param int $id
     */
    public function load(YBCore_Model_Abstract $model, $id)
    {
        $this->_getEntityFromDb($id);
        
        $currentDataRowSet = $this->_getCurrentDataRowSet();
        if (count($currentDataRowSet) > 0)
            $this->_setEntityToModel($model, $currentDataRowSet[0]);
    }

    /**
     * Loads a list of models from db
     * 
     * @return array of YBCore_Model
     */
    protected function _loadModelList()
    {
        $this->_getEntityListFromDb();
        $modelList = array();
        
        $currentDataRowSet = $this->_getCurrentDataRowSet();
        if (count($currentDataRowSet) > 0)
            foreach ($currentDataRowSet as $row)
            {
                $modelClassName = $this->_getModelClassName($row);
                $currentModel = new $modelClassName();
                $this->_setEntityToModel($currentModel, $row);
                $modelList[] = $currentModel;
            }
        return $modelList;
    }

    /** 
     * Deletes the model from db
     * 
     * @param int $id
     */
    public function delete($id)
    {
        $whereClause = $this->_createWhereClause($id, 'id');
        $this->_setWhereClauseList($whereClause);
        
        $this->_deleteModelList();
    }

    /**
     * Deletes a list of models from db
     * 
     */
    protected function _deleteModelList()
    {
        $dbTableClassName = $this->_getDbTableClassName();
        $table = new $dbTableClassName();
        
        $whereClause = $this->_retrieveWhereClause();
        $table->delete($whereClause);
    }

    /** 
     * Saves the model to db
     * 
     * @param YBCore_Model_Abstract $model
     */
    public function save(YBCore_Model_Abstract $model)
    {
        $dbTableClassName = $this->_getDbTableClassName();
        $table = new $dbTableClassName();
        
        $data = $this->_setModelToEntity($model);
        
        if (null === $model->getId())
        {
            $id = $table->insert($data);
            $model->setId($id);
        } else
        {
            $whereClause = $this->_createWhereClause($model->getId(), 'id');
            $this->_setWhereClauseList($whereClause);
            $whereClause = $this->_retrieveWhereClause();
            $table->update($data, $whereClause);
        }
    }

    /**
     * Gets the corresponding row from db then sets it to _currentDataRowSet
     * 
     * @param int $id
     */
    protected function _getEntityFromDb($id)
    {
        $this->_clearCurrentDataRowSet();
        
        $dbTableClassName = $this->_getDbTableClassName();
        $table = new $dbTableClassName();
        
        $rowSet = $table->find($id);
        if ($rowSet->count() > 0)
            $this->_addToCurrentDataRowSet($rowSet[0]);
    }

    /**
     * Gets the corresponding rowset from db then sets it to _currentDataRowSet
     * 
     * @param string|int|array $dbValue
     * @param string $columnName
     */
    protected function _getEntityListFromDb()
    {
        $this->_clearCurrentDataRowSet();
        
        $dbTableClassName = $this->_getDbTableClassName();
        $table = new $dbTableClassName();
        
        $select = $this->_createSelectStatement($table);
        $rowSet = $table->fetchAll($select);
        
        if ($rowSet->count() > 0)
            foreach ($rowSet as $entity)
                $this->_addToCurrentDataRowSet($entity);
    }

    /**
     * Sets the given entity to the model
     *
     * @param YBCore_Model_Abstract $model
     * @param dbRow $entity
     */
    protected function _setEntityToModel(YBCore_Model_Abstract $model, $entity)
    {
        foreach ($this->_getMapList() as $map)
        {
            $columnName = $map->getEntityColumnName();
            $dbValue = $entity->$columnName;
            
            $setter = $map->getSetterMethodName();
            $model->$setter($dbValue);
        }
    }

    /**
     * Creates the update/insert data from model
     *
     * @param YBCore_Model_Abstract $model
     * @return array 
     */
    protected function _setModelToEntity(YBCore_Model_Abstract $model)
    {
        $data = array();
        foreach ($this->_getMapList() as $map)
        {
            $columnName = $map->getEntityColumnName();
            $getter = $map->getGetterMethodName();
            $dbValue = $model->$getter();
            $data[$columnName] = $dbValue;
        }
        return $data;
    }

    /**
     * Sets the $_currentDataRowSet.
     *
     * @param array of dbRow $currentDataRowSet
     */
    private function _setCurrentDataRowSet(array $currentDataRowSet)
    {
        $this->_currentDataRowSet = $currentDataRowSet;
    }

    /**
     * Adds entity to _currentDataRowSet.
     *
     * @param dbRow $entity
     */
    private function _addToCurrentDataRowSet($entity)
    {
        $currentDataRowSet = $this->_getCurrentDataRowSet();
        $currentDataRowSet[] = $entity;
        $this->_setCurrentDataRowSet($currentDataRowSet);
    }

    /**
     * Clears the $_currentDataRowSet.
     *
     */
    private function _clearCurrentDataRowSet()
    {
        $this->_setCurrentDataRowSet(array());
    }

    /**
     * Gets the $_currentDataRowSet.
     *
     * @return array of $entityClassName
     */
    protected function _getCurrentDataRowSet()
    {
        return $this->_currentDataRowSet;
    }

    /**
     * Sets the $_mapList.
     *
     * @param array of YBCore_Mapper_Map $mapList
     */
    protected function _setMapList($mapList)
    {
        $this->_mapList = $mapList;
    }

    /**
     * Gets the $_mapList.
     *
     * @return array of YBCore_Mapper_Map
     */
    private function _getMapList()
    {
        return $this->_mapList;
    }

    /**
     * Adds the given mapList to the current $_mapList.
     * used in child mappers
     *
     * @todo delete if not used
     *
     * @param array of YBCore_Mapper_Map $mapList
     */
    protected function _addToMapList($mapList)
    {
        $mapList = array_merge($this->_getMapList(), $mapList);
        $this->_setMapList($mapList);
    }

    /**
     * Sets the $_dbTableClassName
     *
     * @param string $dbTableClassName
     */
    protected function _setDbTableClassName($dbTableClassName)
    {
        $this->_dbTableClassName = $dbTableClassName;
    }

    /**
     * Gets the dbtable class name of this mapper
     *
     * @todo instead of returning the classname return (and store) the reference to the table(except for the static calls) 
     * @return string
     */
    private function _getDbTableClassName()
    {
        if (null === $this->_dbTableClassName)
        {
            $tmpArr = explode("_", get_class($this));
            $tmpArr[count($tmpArr) - 2] = "DbTable";
            $this->_setDbTableClassName(implode("_", $tmpArr));
        }
        return $this->_dbTableClassName;
    }

    /**
     * Sets the $_modelClassName.
     *
     * @param string $modelClassName
     */
    public function setModelClassName($modelClassName)
    {
        $this->_modelClassName = $modelClassName;
    }

    /**
     * Gets the $_modelClassName.
     * $entity parameter used only in overridden methods
     * in order to decide which model to be  instantiated
     *
     * @param dbRow $entity
     * @return string
     */
    protected function _getModelClassName($entity)
    {
        return $this->_modelClassName;
    }

    /**
     * Gets the column name of a given class member
     * Finds the column name from maplist
     *
     * @param string $memberName
     * @return string
     */
    private function _getColumnName($memberName)
    {
        foreach ($this->_getMapList() as $map)
            if ($map->getModelMemberName() === $memberName)
                return $map->getEntityColumnName();
    }

    /**
     * Creates the sql query of the current fetch 
     * 
     * @param Zend_Db_Table_Abstract $table
     * @param string|int|array $dbValue
     * @param string $columnName
     * @return Zend_Db_Table_Select
     */
    private function _createSelectStatement($table)
    {
        $select = $table->select();
        
        $whereClause = $this->_retrieveWhereClause();
        if (null !== $whereClause)
            $select->where($whereClause);
        
        $orderClause = $this->_retrieveOrderClause();
        if (null !== $orderClause)
            $select->order($orderClause);
        
        return $select;
    }

    /**
     * Sets the $_orderClauseList.
     *
     * @param array of string $orderClauseList
     */
    private function _setOrderClauseList(array $orderClauseList)
    {
        $this->_orderClauseList = $orderClauseList;
    }

    /**
     * Gets the $_orderClauseList.
     *
     * @return array of string
     */
    private function _getOrderClauseList()
    {
        return $this->_orderClauseList;
    }

    /**
     * Clears the $_orderClauseList.
     *
     */
    private function _clearOrderClauseList()
    {
        $this->_orderClauseList = array();
    }

    /**
     * Adds to $_orderClauseList.
     *
     * @param string $memberName
     * @param [optional] self::SORTDIRECTION_* $sortDirection = self::SORTDIRECTION_ASC 
     */
    protected function _addToOrderClauseList($memberName, $sortDirection = self::SORTDIRECTION_ASC)
    {
        $columnName = $this->_getColumnName($memberName);
        
        $currentList = $this->_getOrderClauseList();
        $currentList[$columnName] = $sortDirection;
        $this->_setOrderClauseList($currentList);
    }

    /**
     * Inserts order clauses
     * Clears the $_orderClauseList once it is used
     * 
     * @return array
     */
    private function _retrieveOrderClause()
    {
        $orderList = array();
        foreach ($this->_getOrderClauseList() as $key => $value)
            $orderList[] = $key . " " . $value;
        
        if (false === empty($orderList))
            $this->_clearOrderClauseList();
        return $orderList;
    }

    /**
     * Sets the $_whereClauseList.
     *
     * @param array of string $whereClauseList
     */
    protected function _setWhereClauseList(array $whereClauseList)
    {
        $this->_whereClauseList = $whereClauseList;
    }

    /**
     * Gets the $_whereClauseList.
     *
     * @return array of string
     */
    private function _getWhereClauseList()
    {
        return $this->_whereClauseList;
    }

    /**
     * Clears the $_whereClauseList.
     *
     */
    private function _clearWhereClauseList()
    {
        $this->_whereClauseList = array();
    }

    /**
     * Returns an atomic to where ClauseList.
     * @todo make a shortcut to create a where clause to look for a value in multiple cols.
     *
     * @param string|int|array $dbValue
     * @param string $memberName
     * @param [optional] self::WHEREOPERATOR_* $whereOperator = self::WHEREOPERATOR_IN
     * @return array of string 
     */
    protected function _createWhereClause($dbValue, $memberName, $whereOperator = self::WHEREOPERATOR_IN)
    {
        if (true === empty($dbValue))
            $dbValue = array();
        if (!is_array($dbValue))
            $dbValue = (array) $dbValue;
        $columnName = $this->_getColumnName($memberName);
        return array($columnName, $whereOperator, $dbValue);
    }

    /**
     * Combines given atomic where clauses.
     *
     * @param array of string $whereClauseList
     * @param [optional] self::LOGICOPERATOR_* $whereOperator = self::LOGICOPERATOR_AND
     * @return array of string 
     */
    protected function _combineWhereClauseList(array $whereClauseList, $logicOperator = self::LOGICOPERATOR_AND)
    {
        return array($whereClauseList, $logicOperator);
    }

    /**
     * Retrieves the where clauses
     * Clears the $_whereClauseList once it is used
     * 
     * @return string
     */
    private function _retrieveWhereClause()
    {
        $whereClause = null;
        $whereClauseList = $this->_getWhereClauseList();
        if (false === empty($whereClauseList))
        {
            $whereClause = $this->_resolveWhereClauseList($whereClauseList);
            $this->_clearWhereClauseList();
        }
        return $whereClause;
    }

    /**
     * Resolves the where clause list 
     * 
     * @param array of string $whereClauseList
     * @return string 
     */
    private function _resolveWhereClauseList($whereClauseList)
    {
        $query = '';
        if (3 === count($whereClauseList)) // atomic
        {
            $columnName = $whereClauseList[0];
            $whereOperator = $whereClauseList[1];
            $dbValue = $whereClauseList[2];
            
            if (true === empty($dbValue))
            {
                switch ($whereOperator)
                {
                    case self::WHEREOPERATOR_IN:
                        $query .= self::STR_FALSE;
                        break;
                    case self::WHEREOPERATOR_NOT_IN:
                        $query .= self::STR_TRUE;
                        break;
                }
            } else
            {
                $value = $this->_sanitizeDbValue($dbValue);
                $query .= $columnName . self::STR_WHITESPACE;
                $query .= $whereOperator . self::STR_WHITESPACE;
                $query .= self::STR_LEFTPARENTHESIS . $value . self::STR_RIGHTPARENTHESIS;
            }
        
        } elseif (2 === count($whereClauseList)) // combined
        {
            $subClauseList = $whereClauseList[0];
            $logicOperator = $whereClauseList[1];
            
            foreach ($subClauseList as $subClause)
            {
                if (false === empty($query))
                    $query .= self::STR_WHITESPACE . $logicOperator . self::STR_WHITESPACE;
                $query .= $this->_resolveWhereClauseList($subClause);
            }
            $query = self::STR_LEFTPARENTHESIS . $query . self::STR_RIGHTPARENTHESIS;
        }
        return $query;
    }

    /**
     * Sanitizes the given value from sql injections by using Zend_Db_Adapter quote
     * 
     * @param string|int|array $dbValue
     * @return string
     */
    private function _sanitizeDbValue($dbValue)
    {
        $dbTableClassName = $this->_getDbTableClassName();
        $dbAdapter = $dbTableClassName::getDefaultAdapter();
        return $dbAdapter->quote($dbValue);
    }

    /**
     * Gets the mapping of the given model property
     * 
     * @param string $modelPoperty
     * @return YBCore_Mapper_Map
     */
    public function getMap($modelPoperty)
    {
        foreach ($this->_getMapList() as $map)
        {
            if ($modelPoperty === $map->getModelMemberName())
                return $map;
        }
    }
}