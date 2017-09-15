<?php

namespace Restaurant\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;

class City extends AbstractModel {
	public $id;
	public $state_id;
	public $country_id;
	public $city_name;
	public $state_code;
	public $latitude;
	public $longitude;
	public $sales_tax;
	public $status;
	public $time_zone;
	protected $_db_table_name = 'Home\Model\DbTable\CityTable';
	protected $_primary_key = 'id';
	public function getCity(array $options = array()) {
		return $this->find ( $options )->current ();
	}
	public function fetchCityDetails($city_id = 0) {
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$select->columns ( array (
				'id',
                'city_name',
                'state_code',
                'latitude',
                'longitude',
                'time_zone',
                'neighbouring',
                'sales_tax' 
		) );
		$select->join ( array (
				's' => 'states' 
		), 'cities.state_id = s.id', array (
				'country_id',
				'state',
				'state_code' 
		), $select::JOIN_LEFT );
		$select->join ( array (
				'c' => 'countries' 
		), 's.country_id = c.id', array (
				'country_name',
				'country_short_name' 
		), $select::JOIN_LEFT );
		$select->where ( array (
				'cities.id' => $city_id 
		) );
		$readGateway = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ();
		$cityDetails = $readGateway->selectWith ( $select )->current ()->getArrayCopy ();
		return $cityDetails;
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
