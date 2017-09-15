<?php

namespace Restaurant\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;


class  RestaurantDineinCalendars extends AbstractModel {
	
	public $id;
	public $restaurant_id;
	public $breakfast_start_time;
	public $breakfast_end_time;
	public $lunch_start_time;
	public $lunch_end_time;
	public $dinner_start_time;
	public $dinner_end_time;
	public $breakfast_seats;
	public $lunch_seats;
	public $dinner_seats;
	public $dinningtime_small;
	public $dinningtime_large;
	public $updatedAt;
	public $status;

	
	protected $_db_table_name = 'Restaurant\Model\DbTable\RestaurantDineinCalendarsTable';
	public function findRestaurantDineinDetail(array $options = array()) {	     
		$dineinDetailObj = $this->find ( $options )->current ();		
		if($dineinDetailObj)
			$dineinDetail=$dineinDetailObj->toArray();
		else 
			$dineinDetail = array();
		
		return $dineinDetail;
	}
	public function getDineinTime($restaurant_id = 0) {
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$select->columns ( array (
				'breakfast_start_time',
				'breakfast_end_time',
				'lunch_start_time',
				'lunch_end_time',
				'dinner_start_time',
				'dinner_end_time'
		) );
				
		$select->where ( array (
				'restaurants.id' => $restaurant_id 
		) );
		//var_dump($select->getSqlString($this->getPlatform('READ')));die;
		$dineinTime = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select );
		return $dineinTime;
	}
	public function getDineinSeats($restaurant_id = 0) {
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$select->columns ( array (
				'breakfast_seats',
				'lunch_seats',
				'dinner_seats'
		) );
				
		$select->where ( array (
				'restaurants.id' => $restaurant_id 
		) );
		//var_dump($select->getSqlString($this->getPlatform('READ')));die;
		$seats = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select );
		return $seats;
	}
	

}