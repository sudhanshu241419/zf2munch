<?php

namespace Restaurant\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;

class Cuisine extends AbstractModel {
	public $id;
	public $cuisine;
	public $cuisine_type;
	public $description;
	public $image_name;
	public $created_on;
	public $search_status;
	public $status;
	public $priority;
	const STATUS_INACTIVE = '0';
	const STATUS_ACTIVE = '1';
	protected $_db_table_name = 'Restaurant\Model\DbTable\CuisineTable';
	protected $_primary_key = 'id';
	public function getRestaurantCuisine(array $options = array()) {
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$select->columns ( array (
				'cuisine_id' 
		) );
		
		$select->join ( array (
				'cu' => 'cuisines' 
		), 'cu.id = restaurant_cuisines.cuisine_id', array (
				'cuisine' 
		), $select::JOIN_INNER );
		$select->where ( array (
				'restaurant_cuisines.restaurant_id' => $options ['columns'] ['restaurant_id'],
				'cu.status' => self::STATUS_ACTIVE,
				'restaurant_cuisines.status' => self::STATUS_ACTIVE 
		) );
		$cuisines = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select );
        return $cuisines;
	}
    
    public function getRestaurantCuisineDetails(array $options = array()) {
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$select->columns ( array (
				'cuisine_id' 
		) );
		
		$select->join ( array (
				'cu' => 'cuisines' 
		), 'cu.id = restaurant_cuisines.cuisine_id', array (
				'cuisine' 
		), $select::JOIN_INNER );
		$select->where ( array (
				'restaurant_cuisines.restaurant_id' => $options ['columns'] ['restaurant_id'],
				'cu.status' => self::STATUS_ACTIVE,
				'restaurant_cuisines.status' => self::STATUS_ACTIVE 
		) );
        $cuisines = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select )->current ();
        return $cuisines;
	}
    
    public function getRandRestaurantCuisineDetails(array $options = array()) {
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$select->columns ( array (
				'cuisine_id' 
		) );
		
		$select->join ( array (
				'cu' => 'cuisines' 
		), 'cu.id = restaurant_cuisines.cuisine_id', array (
				'cuisine' 
		), $select::JOIN_INNER );
		$select->where ( array (
				'restaurant_cuisines.restaurant_id' => $options ['columns'] ['restaurant_id'],
				'cu.status' => self::STATUS_ACTIVE,
				'restaurant_cuisines.status' => self::STATUS_ACTIVE 
		) );
        $select->order ( new \Zend\Db\Sql\Expression('RAND()'));
        $select->limit (5);
        $cuisines = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select )->toArray ();
        return $cuisines;
	}
    
}