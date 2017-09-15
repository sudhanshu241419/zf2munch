<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;
use Restaurant\Model\Config;

class ConfigController extends AbstractRestfulController {
	public function get($hostname = '') {
		$response = array ();
		$configModel = new Config ();
		
		return $configModel->getConfig ( array (
				
				'where' => array (
						'hostname' => $hostname 
				) 
		) );
	}
}