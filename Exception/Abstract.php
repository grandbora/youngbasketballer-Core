<?php
/**
 * Abstract Exception Class
 */
class YBCore_Exception_Abstract extends Exception
{

    /**
     * creates the message
     */
    function __construct()
    {
        $this->message = get_class($this) . ' thrown at ' . $this->getFile() . ':' . $this->getLine();
    }

}