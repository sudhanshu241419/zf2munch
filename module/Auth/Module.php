<?php

namespace Auth;

use Auth\Model\DbTable\AuthTable;
use Zend\Mvc\MvcEvent;

/**
 * Module Management for rest api
 *
 * @author tirth
 * @namespace Auth
 */
class Module extends \MCommons\Module {
	protected $_namespace = __NAMESPACE__;
	protected $_dir = __DIR__;
	
	// Add this method:
	public function getServiceConfig() {
		return array (
			'factories' => array (
				'Auth\Model\DbTable\AuthTable' => function ($sm) {
					$table = new AuthTable ();
					$table->setServiceLocator ( $sm );
					return $table;
				} 
			) 
		);
	}
    
    /**
     * 
     * @param MvcEvent $e
     */
	public function checkAndAddRedis($e) {
		$redis_cache = false;
		$sl = $e->getApplication ()->getServiceManager ();
		$config = $sl->get('config');
		if(class_exists('\Redis') && isset($config['constants']['redis']) && !empty($config['constants']['redis']) && $config['constants']['redis']['enabled']) {
			$redisConfig = $config['constants']['redis'];
			try {
				$redis_options = new \Zend\Cache\Storage\Adapter\RedisOptions();
				$redis_options->setServer($redisConfig);
				$redis_options->setLibOptions(array(
			        \Redis::OPT_SERIALIZER => \Redis::SERIALIZER_PHP
				));

				$redis_cache = new \Zend\Cache\Storage\Adapter\Redis($redis_options);
				$space = $redis_cache->getTotalSpace();
			} catch (\Exception $ex) {
				$redis_cache = false; 
			}
		}
		$sl->setService("RedisCache" , $redis_cache);
	}
    
	/**
     * Higher the priority in the events they are executed earlier.
	 * Negative priorites allowed
     * @param \Zend\Mvc\MvcEvent $e
     */
	public function onBootstrap($e) {
		/* @var $moduleManager \Zend\ModuleManager\ModuleManager */
		$moduleManager = $e->getApplication ()->getServiceManager ()->get ( 'modulemanager' );
		
		/* @var $sharedEvents \Zend\EventManager\SharedEventManager */
		$sharedEvents = $moduleManager->getEventManager ()->getSharedManager ();
		
		/**
		 * Redis Initialization and adding it as service 
		 */
		$sharedEvents->attach ( 'Zend\Mvc\Controller\AbstractRestfulController', MvcEvent::EVENT_DISPATCH, array (
				$this,
				'checkAndAddRedis' 
		), 997 );
	}
}