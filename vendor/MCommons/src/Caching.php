<?php

namespace MCommons;

class Caching {
	private static $instance = false;
	public static function getInstance() {
		if (! static::$instance) {
			static::$instance = new static ();
		}
		return static::$instance;
	}

	public function set($item, $value,$time = false) {
		$config = StaticOptions::getServiceLocator()->get('config');
		$options = $config['caches']['memcached']['adapter']['options'];
		$options['ttl'] = $time;		
		$memcachedAdapterOptions = new \Zend\Cache\Storage\Adapter\MemcachedOptions ($options);
		$cache = new \Zend\Cache\Storage\Adapter\Memcached ( $memcachedAdapterOptions );
		return $cache->setItem ( $item, $value );
	}
	public function get($item) {
		return StaticOptions::getServiceLocator ()->get ( 'memcached' )->getItem ( $item );
	}
}