<?php

namespace Restaurant\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;

class Gallery extends AbstractModel {
	public $id;
	public $restaurant_id;
	public $image;
	public $image_url;
	public $image_type;
	public $status;
	public $image_dimension;
	protected $_db_table_name = 'Restaurant\Model\DbTable\GalleryTable';
	protected $_primary_key = 'id';
	const NORMAL = 'N';
	const CONSOLIDATED = 'C';
	const REVIEW_STATUS = 1;
	public function getGallery($restaurant_id = 0, $limit = 0) {
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$select->columns ( array (
				new Expression ( 'DISTINCT(`image`) as image' ) 
		) );
		
		$select->join ( array (
				'r' => 'restaurants' 
		), 'r.id = restaurant_images.restaurant_id', array (
				'rest_code' 
		), $select::JOIN_LEFT );
		
		$select->where ( array (
				'restaurant_images.restaurant_id' => $restaurant_id,
				'restaurant_images.status' => 1 
		) );
		if ($limit)
			$select->limit ( $limit );
		$gallery = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select );
		
		return $gallery;
	}
    
    public function getRestaurantGallery($restaurant_id = 0) {
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$select->columns ( array (
				new Expression ( 'DISTINCT(`image`) as image' ) 
		) );
		$select->where ( array (
				'restaurant_images.restaurant_id' => $restaurant_id,
				'restaurant_images.status' => 1 
		) );
        $select->order(new Expression ( 'RAND()' ));
        $select->limit(1);
        //pr($select->getSqlString($this->getPlatform('READ')),true); 
		$gallery = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select )->toArray();
		//pr($gallery,true);
		return $gallery;
	}
    
    public function hasGallery($restaurant_id){
        $select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$select->columns ( array ('id') );
		$select->where ( array (
				'restaurant_images.restaurant_id' => $restaurant_id,
				'restaurant_images.status' => 1 
		) );        
        $select->limit(1);
        //pr($select->getSqlString($this->getPlatform('READ')),true); 
		$gallery = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select )->toArray();
		//pr($gallery,true);
		return $gallery;
    }
}