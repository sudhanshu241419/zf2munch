<?php
namespace MCommons\Db\Adapter;

use Zend\Db\Adapter\Adapter;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Adapter\AdapterServiceFactory;

class ReadAdapterServiceFactory extends AdapterServiceFactory
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
        $slaves = isset($db['slave']) ? $db['slave'] : array();
        $randSlave = array_rand($slaves);
        return new Adapter($slaves[$randSlave]);
    }
}
