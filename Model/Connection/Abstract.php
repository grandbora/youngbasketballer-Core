<?php
/**
 * Connection abstract class
 * A connection exist between an employer and employee, which can be a contract, offer or a request
 * 
 */
abstract class YBCore_Model_Connection_Abstract extends YBCore_Model_Abstract
{

    /**
     * id of the connection
     *
     * @var int
     */
    private $_id;

    /**
     * id of the employer of the connection
     *
     * @var int
     */
    private $_employerId;

    /**
     * employer of the connection
     *
     * @var YBCore_Interface_Employer
     */
    private $_employer;

    /**
     * id of the employee of the connection
     *
     * @var int
     */
    private $_employeeId;

    /**
     * employee of the connection
     *
     * @var YBCore_Interface_Employee
     */
    private $_employee;

    /**
     * salary 
     *
     * @var int
     */
    private $_salary;

    /**
     * type of the connection 
     * either team or individual
     *
     * @var int YBCore_Model_Connection_Mapper_Connection::TYPE_*
     */
    private $_type;

    /**
     * type of the connection finalized|offer|request| 
     *
     * @var int YBCore_Model_Connection_Mapper_*::STATUS
     */
    private $_status;

    /**
     * days of the connection
     *
     * @var array of YBCore_Model_Connection_Day
     */
    private $_dayList = array();

    /**
     * When connections are loaded one by one their days wont be loaded!
     * To load with days call the load connection list method 
     * of the owner object(team, user) must be called
     * 
     * @param [optional] int id
     * sets the status
     */
    public function __construct($id = null)
    {
        $status = $this->_getMapperConstant('STATUS');
        $this->setStatus($status);
        
        // read above phpdoc
        if (null !== $id)
            $this->_load((int) $id);
    }

    /**
     * Sets the $_id.
     *
     * @param int $id
     */
    public function setId($id)
    {
        $this->_id = (int) $id;
    }

    /**
     * Gets the $_id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Sets the $_employerId.
     *
     * @param int $employerId 
     */
    public function setEmployerId($employerId)
    {
        $this->_clearEmployer();
        $this->_employerId = (int) $employerId;
    }

    /**
     * Gets the $_employerId.
     *
     * @return int
     */
    public function getEmployerId()
    {
        return $this->_employerId;
    }

    /**
     * Sets the $_employer.
     *
     * @param YBCore_Interface_Employer $employer 
     */
    private function _setEmployer(YBCore_Interface_Employer $employer)
    {
        $this->_employer = $employer;
    }

    /**
     * Gets the $_employer.
     *
     * @return YBCore_Interface_Employer
     */
    public function getEmployer()
    {
        if (null === $this->_employer)
        {
            $employerId = $this->getEmployerId();
            if (null !== $employerId)
            {
                $employer = null;
                switch ($this->getType())
                {
                    case YBCore_Model_Connection_Mapper_Connection::TYPE_INDIVIDUAL:
                        $employer = new YBCore_Model_User_Player($employerId);
                        break;
                    case YBCore_Model_Connection_Mapper_Connection::TYPE_TEAM:
                        $employer = new YBCore_Model_Team($employerId);
                        break;
                }
                $this->_setEmployer($employer);
            }
        }
        return $this->_employer;
    }

    /**
     * Clears the $_employer.
     *
     */
    private function _clearEmployer()
    {
        $this->_employerId = null;
        $this->_employer = null;
    }

    /**
     * Sets the $_employeeId.
     *
     * @param int $employeeId 
     */
    public function setEmployeeId($employeeId)
    {
        $this->_clearEmployee();
        $this->_employeeId = (int) $employeeId;
    }

    /**
     * Gets the $_employeeId.
     *
     * @return int
     */
    public function getEmployeeId()
    {
        return $this->_employeeId;
    }

    /**
     * Sets the $_employee.
     *
     * @param YBCore_Interface_Employee $employee 
     */
    private function _setEmployee(YBCore_Interface_Employee $employee)
    {
        $this->_employee = $employee;
    }

    /**
     * Gets the $_employee.
     *
     * @return YBCore_Interface_Employee
     */
    public function getEmployee()
    {
        if (null === $this->_employee)
        {
            $employeeId = $this->getEmployeeId();
            if (null !== $employeeId)
            {
                $userMapper = YBCore_Mapper_Factory::getMapper('YBCore_Model_User_Abstract');
                $employeeList = $userMapper->initializeUser($employeeId);
                $this->_setEmployee($employeeList[0]);
            }
        }
        
        return $this->_employee;
    }

    /**
     * Clears the $_employee.
     *
     */
    private function _clearEmployee()
    {
        $this->_employeeId = null;
        $this->_employee = null;
    }

    /**
     * Sets the $_salary.
     *
     * @param int $salary 
     */
    public function setSalary($salary)
    {
        $this->_salary = (int) $salary;
    }

    /**
     * Gets the $_salary.
     *
     * @return int
     */
    public function getSalary()
    {
        return $this->_salary;
    }

    /**
     * Sets the $_type.
     *
     * @param int $type 
     */
    public function setType($type)
    {
        $this->_type = (int) $type;
    }

    /**
     * Gets the $_type.
     *
     * @return int
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Sets the $_status.
     *
     * @param int $status 
     */
    public function setStatus($status)
    {
        $this->_status = (int) $status;
    }

    /**
     * Gets the $_status.
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->_status;
    }

    /**
     * Sets the $_dayList.
     *
     * @param array of YBCore_Model_Connection_Day $dayList 
     */
    public function setDayList(array $dayList)
    {
        $this->_dayList = $dayList;
    }

    /**
     * Gets the $_dayList.
     *
     * @return array of YBCore_Model_Connection_Day
     */
    public function getDayList()
    {
        return $this->_dayList;
    }

    /**
     * Saves all days of the connection
     * It is not possibel to make a bulk insert. Therefore items are saved in a loop. 
     * (Actually possible but need further implementation, --impossible is nothing,)
     */
    public function saveDayList()
    {
        foreach ($this->getDayList() as $day)
        {
            $day->setConnectionId($this->getId());
            $day->save();
        }
    }

    /**
     * Removes all days of the connection
     * 
     */
    public function deleteDayList()
    {
        $connectionDayMapper = YBCore_Mapper_Factory::getMapper('YBCore_Model_Connection_Day');
        $connectionDayMapper->deleteByConnectionId($this->getId());
        
        //clear deleted days
        $dayList = $this->setDayList(array());
    }

    /**
     * Removes a day of the connection
     *
     * @param YBCore_Model_Connection_Day $connectionDay
     */
    public function removeDay($connectionDay)
    {
        $connectionDayMapper = YBCore_Mapper_Factory::getMapper('YBCore_Model_Connection_Day');
        $connectionDayMapper->deleteById($connectionDay->getId());
        
        //clear deleted days
        $dayList = $this->getDayList();
        for ($i = 0; $i < count($dayList); $i++)
            if ($dayList[$i]->getId() === $connectionDay->getId())
                unset($dayList[$i]);
    }

    /**
     * Adds a day to the connection
     *
     * @param YBCore_Model_Connection_Day $connectionDay
     */
    public function addDay($connectionDay)
    {
        $connectionDay->save();
        $dayList = $this->getDayList();
        $dayList[] = $connectionDay;
        $this->setDayList($dayList);
    }
}