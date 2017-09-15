<?php

namespace Search\Model;

use MCommons\Model\AbstractModel;
use MCommons\StaticOptions;
use Zend\Http\Client;
use Zend\Json\Json;

class Search extends AbstractModel {
	
	// protected $_db_table_name = 'Restaurant\Model\DbTable\RestaurantTable';
	protected $_primary_key = 'id';
	public function findSearch(array $params = array()) {
		$httpClient = new Client (StaticOptions::getSolrUrl()."hbrestaurant/hbsearch?");
		$httpClient->setParameterGet ($params);
		
		$response = $httpClient->dispatch ( $httpClient->getRequest () );
		if ($response->isSuccess ()) {
			return Json::decode ( $response->getBody (), Json::TYPE_ARRAY );
		} else {
			return StaticOptions::getResponse ( $this->getServiceLocator (), array (
					'error' => 'Resturant not found' 
			), 404 );
		}
	}
	public function cuisineCounter(array $params = array()) {
		$httpClient = new Client ( SOLAR_RESTUARANT_COUNTER_URL );
		
		$httpClient->setParameterGet ( $params );
		
		$response = $httpClient->dispatch ( $httpClient->getRequest () );
		
		if ($response->isSuccess ()) {
			return Json::decode ( $response->getBody (), Json::TYPE_ARRAY );
		} else {
			return StaticOptions::getResponse ( $this->getServiceLocator (), array (
					'error' => 'Cuisine not found' 
			), 404 );
		}
	}
	public function check_near_time($hi) {
		$arrNearTime = array ();
		$arrHI = explode ( ":", $hi );
		$hours = $arrHI [0];
		$actual_minutes = $arrHI [1];
		$minutes = $actual_minutes;
		
		if ($minutes < 30) {
			$hours = $hours + 1;
			$minutes = 00;
		} else {
			$hours = $hours + 1;
			$minutes = 30;
		}
		if ($hours < 10) {
			$hours = '0' . $hours;
		}
		if ($minutes < 10) {
			$minutes = '0' . $minutes;
		}
		
		$arrNearTime ['near_time'] = $hours . ':' . $minutes;
		$arrNearTime ['actual_minutes'] = $actual_minutes;
		return $arrNearTime;
	}
}