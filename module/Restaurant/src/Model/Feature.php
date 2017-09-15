<?php

namespace Restaurant\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Where;

class Feature extends AbstractModel {
	public $id;
	public $restaurant_id;
	public $feature_id;
	public $status;
	protected $_db_table_name = 'Restaurant\Model\DbTable\FeatureTable';
	protected $_primary_key = 'id';
	public function getFeature(array $options = array()) {
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$select->columns ( array () );
		
		$select->columns ( array (
				'id',
				'features',
				'feature_type',
				'features_key' 
		) );
		
		$select->where ( array (
				'search_status' => 1 
		) );
		
		$featureData = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select );
		
		return $featureData;
	}
	public function getRestaurantTop($restaurant_id = 0) {
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$select->columns ( array (
				'id' => 'feature_id' 
		) );
		
		$select->join ( array (
				'f' => 'features' 
		), 'f.id = restaurant_features.feature_id', array (
				'name' => 'features' 
		), $select::JOIN_INNER );
		
		$where = new Where ();
		$where->equalTo ( 'restaurant_features.restaurant_id', $restaurant_id );
		$where->equalTo ( 'f.status', 1 );
		$where->equalTo ( 'restaurant_features.status', 1 );
		//$where->notEqualTo ( 'f.features', 'Dinner' );
		//$where->notEqualTo ( 'f.features', 'Lunch' );
		$where->notEqualTo ( 'f.features', 'Breakfast' );
		$where->notEqualTo ( 'f.features', 'Takeout' );
		$where->notEqualTo ( 'f.features', 'Delivery' );
		
		$select->where ( $where );
		$select->order ( new Expression ( 'RAND()' ) );
		$features = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select );
		return $features;
	}
	public function getRestaurantPlaceFeatures($restaurant_id, $featureType = NULL) {
		$select = new Select ();
		$limit = 5;
		$select->from ( $this->getDbTable ()->getTableName () );
		$select->columns ( array () );
		
		$select->join ( array (
				'f' => 'features' 
		), 'f.id = restaurant_features.feature_id', array (
				'features' 
		), $select::JOIN_INNER );
		
		$select->where ( array (
				'restaurant_features.restaurant_id' => $restaurant_id,
				'restaurant_features.status' => 1,
				'f.feature_type' => $featureType 
		) );
		$select->order ( new Expression ( 'RAND()' ) );
		$select->limit ( 5 );
		
		$restaurantFeature = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select );
		
		return $restaurantFeature;
	}

    public function getRestaurantPlaceFeaturesDetails($restaurant_id, $featureType = NULL) {
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$select->columns ( array () );
		$select->join ( array (
				'f' => 'features' 
		), 'f.id = restaurant_features.feature_id', array (
				'features' 
		), $select::JOIN_INNER );
		$select->where ( array (
				'restaurant_features.restaurant_id' => $restaurant_id,
				'restaurant_features.status' => 1,
				'f.feature_type' => $featureType 
		) );
		$restaurantFeature = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select )->current();
		return $restaurantFeature;
	}
    
    public function getRestaurantPlaceFeaturesDetailOtion($restaurant_id, $featureType = NULL) {
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$select->columns ( array () );
		$select->join ( array (
				'f' => 'features' 
		), 'f.id = restaurant_features.feature_id', array (
				'features' 
		), $select::JOIN_INNER );
		$select->where ( array (
				'restaurant_features.restaurant_id' => $restaurant_id,
				'restaurant_features.status' => 1,
				'f.feature_type' => $featureType 
		) );
        $select->order ( new Expression('RAND()'));
        $select->limit (5);
		$restaurantFeature = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select )->toArray();
		return $restaurantFeature;
	}
    
    
    
}