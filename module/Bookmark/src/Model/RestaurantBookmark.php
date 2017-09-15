<?php

namespace Bookmark\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use MCommons\StaticOptions;

class RestaurantBookmark extends AbstractModel {
	public $id;
	public $restaurant_id;
	public $restaurant_name = "";
	public $user_id;
	public $created_on;
	public $type;
	protected $_db_table_name = 'Bookmark\Model\DbTable\RestaurantBookmarksTable';
	public function getRestaurantBookmarkCount($restaurant_id = 0) {
		$this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' );
		$res = $this->find ( array (
				'columns' => array (
						'total_count' => new Expression ( 'COUNT(restaurant_id)' ),
						'type' 
				),
				'where' => array (
						'restaurant_id' => $restaurant_id 
				),
				'group' => new Expression ( 'type' ) 
		) );
		return $res->toArray ();
	}
	public function addRestaurantBookMark() {
		$data = $this->toArray ();
		$rowsAffected = 0;
		
		$writeGateway = $this->getDbTable ()->getWriteGateway ();
		$rowsAffected = $writeGateway->insert ( $data );
		// Get the last insert id and update the model accordingly
		$lastInsertId = $writeGateway->getAdapter ()->getDriver ()->getLastGeneratedValue ();
		if ($rowsAffected >= 1) {
			$this->id = $lastInsertId;
			$response = $this->toArray ();
			$response = array_intersect_key ( $response, array_flip ( array (
					'id',
					'user_id',
					'type',
					'restaurant_id' 
			) ) );
			return $response;
		}
		return true;
	}
	public function insertBookmark($data){
		$writeGateway = $this->getDbTable ()->getWriteGateway ();
		$rowsAffected = $writeGateway->insert ( $data );
		return true;
	}
	public function isAlreadyBookmark(array $options = array()) {
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$select->columns ( array (
				'id'
		) );
		
		$select->where ( array (
				'type' => $options ['type'],
				'restaurant_id' => $options ['restaurant_id'],
				'user_id' => $options ['user_id'] 
		) );
		
		$response = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select );
		return $response->toArray ();
	}
	public function checkIfUserBookmarked(){
		$userId = StaticOptions::getUserSession ()->getUserId();
		$options = array (
				'where' => array (
						'user_id' => $userId
				),
				'limit' => 1
		);
		$this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' );
		$response = $this->find ( $options )->toArray ();
		$hasBookmarked = (!empty($response))?true:false;
		return $hasBookmarked;
	}
        public function delete(){
           $writeGateway = $this->getDbTable ()->getWriteGateway ();
	   $rowsAffected = $writeGateway->delete ( array (
		'id' => $this->id 
            ) );
            return $rowsAffected;
	}
    public function getRestaurantBookmarkCountOfType($restaurant_id = 0,$type=false) {
		$this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' );
        if($type){
            $res = $this->find ( array (
                'columns' => array (
                  'total_count' => new Expression ( 'COUNT(restaurant_id)' ),						
                 ),
                'where' => array (
                 'restaurant_id' => $restaurant_id,
                 'type'=>$type
                )				
            ) );
        }
		return $res->toArray ();
	}
}//end of class
