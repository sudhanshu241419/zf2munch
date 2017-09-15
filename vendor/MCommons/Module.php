<?php
namespace MCommons;

use MCommons\StaticOptions;

abstract class Module
{

    protected $_namespace = __NAMESPACE__;

    protected $_dir = __DIR__;

    public function getAutoloaderConfig()
    {
        $autoloadClassMap = ($classMapFile = realpath($this->_dir . '/autoload_classmap.php')) !== false ? $classMapFile : array();
        
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    $this->_namespace => $this->_dir . '/src/'
                )
            ),
            'Zend\Loader\ClassMapAutoloader' => array(
                $autoloadClassMap
            )
        );
    }

    public function getConfig()
    {
        if (($file = realpath($this->_dir . '/config/module.config.php')) !== false) {
            return include $file;
        }
        return array();
    }
}