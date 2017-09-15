<?php
namespace MCommons\Db\Adapter;

use Zend\Db\Adapter\Adapter;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Adapter\AdapterServiceFactory;

class WriteAdapterServiceFactory extends AdapterServiceFactory
{

    /**
     * Create db adapter service
     *
     * @param ServiceLocatorInterface $serviceLocator            
     * @return Adapter
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');
        $db = isset($config['db']) ? $config['db'] : array();
        $masters = isset($db['master']) ? $db['master'] : array();
        $randMaster = array_rand($masters);
        return new Adapter($masters[$randMaster]);
    }
}