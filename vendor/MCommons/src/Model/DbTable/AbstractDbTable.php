<?php
namespace MCommons\Model\DbTable;

use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\Db\ResultSet\ResultSet;
use MCommons\StaticOptions;
use Zend\ServiceManager\ServiceLocatorInterface;

abstract class AbstractDbTable implements ServiceLocatorAwareInterface
{

    protected $_serviceLocator;

    protected $_table_name;

    protected $_write_gateway;

    protected $_read_gateway;

    protected $_array_object_prototype;

    public function setArrayObjectPrototype($prototype)
    {
        if (! is_string($prototype)) {
            throw new \Exception("Protoype needs to be string name of the model or class");
        }
        $this->_read_gateway = null;
        $this->_array_object_prototype = $prototype;
        return $this;
    }

    public function getArrayObjectPrototype()
    {
        if (! $this->_array_object_prototype) {
            $this->_array_object_prototype = '\Zend\Stdlib\ArrayObject';
        }
        return $this->_array_object_prototype;
    }

    public function getWriteGateway()
    {
        if (! $this->_write_gateway) {
            $resultSetPrototype = new ResultSet();
            $prototype = $this->getArrayObjectPrototype();
            $resultSetPrototype->setArrayObjectPrototype(new $prototype());
            $dbAdapter = StaticOptions::getDbWriteAdapter();
            $this->_write_gateway = new TableGateway($this->_table_name, $dbAdapter, null, $resultSetPrototype);
        }
        return $this->_write_gateway;
    }

    public function getReadGateway()
    {
        if (! $this->_read_gateway) {
            $resultSetPrototype = new ResultSet();
            $prototype = $this->getArrayObjectPrototype();
            $resultSetPrototype->setArrayObjectPrototype(new $prototype());
            $dbAdapter = StaticOptions::getDbReadAdapter();
            $this->_read_gateway = new TableGateway($this->_table_name, $dbAdapter, null, $resultSetPrototype);
        }
        return $this->_read_gateway;
    }

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->_serviceLocator = $serviceLocator;
        return $this;
    }

    public function getServiceLocator()
    {
        return $this->_serviceLocator;
    }

    public function getTableName()
    {
        if (! $this->_table_name) {
            throw new \Exception("Table name was not set before");
        }
        return $this->_table_name;
    }
}