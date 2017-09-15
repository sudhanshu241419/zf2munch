<?php

namespace Restaurant\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Where;
use User\UserFunctions;

class RestaurantBookmark extends AbstractModel {
	public $id;
	public $restaurant_id;
	public $restaurant_name="";
	public $user_id;
	public $created_on;
	public $type;
	const LIKEIT = 'li';
	const LOVEIT = 'lo';
	const BEENTHERE = 'bt';
	protected $_db_table_name = 'Restaurant\Model\DbTable\RestaurantBookmarksTable';
	public $bookmark_types = array (
			"li",
			"lo",
			"ti",
			"wi",
			"bt",
			"wl" 
	);
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
	public function getLikeContent($restaurant_id, $user_id) {
		$output_likecontent = array ();
		
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$select->columns ( array () );
		
		$select->join ( array (
				'u' => 'users' 
		), 'u.id = restaurant_bookmarks.user_id', array (
				'first_name' 
		), $select::JOIN_INNER );
		
		$select->where ( array (
				'restaurant_bookmarks.restaurant_id' => $restaurant_id,
				'restaurant_bookmarks.type' => self::LOVEIT,
				'u.id' => $user_id 
		) );
		
		$LikeContent = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select )->toArray ();
		
		$count_Likecontent = count ( $LikeContent );
		if ($count_Likecontent > 0) {
			for($i = 0; $i < $count_Likecontent; $i ++) {
				if (! empty ( $LikeContent [$i] )) {
					$output_likecontent [$i] = $LikeContent [$i] ['first_name'];
				}
			}
		} else {
			$output_likecontent = 0;
		}
		// return $output_likecontent;
	} // end of function
	public function getRestaurantBookmarksByUserId($restaurant_id, $user_id) {
		$this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' );
		$res = $this->find ( array (
				'columns' => array (
						'total_count' => new Expression ( 'COUNT(restaurant_id)' ),
						'type' 
				),
				'where' => array (
						'restaurant_id' => $restaurant_id,
						'user_id' => $user_id 
				),
				'group' => new Expression ( 'type' ) 
		) )->toArray ();
		
		if (! empty ( $res )) {
			$bdata = array ();
			foreach ( $res as $bd ) {
				$key = $bd ['type'];
				$bdata [$key] = $bd ['total_count'];
			}
			foreach ( $this->bookmark_types as $type ) {
				if (! array_key_exists ( $type, $bdata ))
					$bdata [$type] = 0;
			}
			return $bdata;
		}
		return array (
				"li" => 0,
				"lo" => 0,
				"wi" => 0,
				"ti" => 0,
				"bt" => 0,
				"re" => 0 
		);
	} // end of function
	public function addRestaurantBookMark() {
		$data = array ();
		
		$data ['type'] = $this->type;
		$data ['restaurant_name'] = $this->restaurant_name;
		$data ['restaurant_id'] = $this->restaurant_id;
		
		$writeGateway = $this->getDbTable ()->getWriteGateway ();
		
		$rowsAffected = 0;
		
		if ($this->user_id) {
			$data ['created_on'] = $this->created_on;
			$data ['user_id'] = $this->user_id;
			$rowsAffected = $writeGateway->insert ( $data );
		}
		
		// Get the last insert id and update the model accordingly
		$lastInsertId = $writeGateway->getAdapter ()->getDriver ()->getLastGeneratedValue ();
		
		if ($rowsAffected >= 1) {
			$this->id = $lastInsertId;
			$response = $this->toArray ();
			$response = array_intersect_key ( $response, array_flip ( array (
					'id',
					'user_id',
					'type',
					'restaurant_name',
					'restaurant_id' 
			) ) );
			return $response;
		}
		return false;
	}
	public function getBookmarkActivity($restaurantId,$userId,$bookmarkType){
		
	}
	public function getUserRestaurantBookmarkCount($userId)
	{
		$select = new Select();
		$select->from($this->getDbTable()
				->getTableName());
		$select->columns(array(
	
				'total_res_bookmark' => new Expression('COUNT(id)')
		));
		$where = new Where();
		$where->equalTo('user_id', $userId);
		
		$select->where($where);
		$totalbookmark = $this->getDbTable()
		->setArrayObjectPrototype('ArrayObject')
		->getReadGateway()
		->selectWith($select)
		->current();
		return $totalbookmark;
	}
	public function getUserBookmarkForRestaurantCount($userId){
		$count=0;
		$userFunction=  new UserFunctions();
		$select = new Select();
		$select->from($this->getDbTable()
				->getTableName());
		$select->join ( array (
				'rs' => 'restaurants'
		), 'rs.id =  restaurant_bookmarks.restaurant_id', array (
				'closed','inactive'
		), $select::JOIN_LEFT );
				
		
		$where = new Where();
		$where->equalTo('user_id', $userId);
		$select->where($where);
        $select->group('restaurant_bookmarks.restaurant_id');
		$totalbookmark = $this->getDbTable()
		->setArrayObjectPrototype('ArrayObject')
		->getReadGateway()
		->selectWith($select)
		->toArray();
		$response = $userFunction->arrangeRestaurantBookmarks ( $totalbookmark );
		$response = $userFunction->getRestaurantBookmarkCounts($response);
		$count = $response['loved_it_count']+$response['been_there_count']+$response['crave_it_count'];
		return $count;
	}
    
    public function addCountToResponse($userId=false) {
		$this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' );
		$resBt = $this->find ( array (
				'columns' => array (
						'total_count_bt' => new Expression ( 'COUNT(DISTINCT restaurant_bookmarks.restaurant_id)' )
				),
				'where' => array (
						'restaurant_bookmarks.user_id' => $userId,
                        'restaurant_bookmarks.type' => 'bt'
				)
                                    
		) );
       $test1=$resBt->toArray ();
       $resWl = $this->find ( array (
				'columns' => array (
						'total_count_wl' => new Expression ( 'COUNT(DISTINCT restaurant_bookmarks.restaurant_id)' )
				),
				'where' => array (
						'restaurant_bookmarks.user_id' => $userId,
                        'restaurant_bookmarks.type' => 'wl'
				) 
		) );
       $test2=$resWl->toArray ();
       $resLo = $this->find ( array (
				'columns' => array (
						'total_count_lo' => new Expression ( 'COUNT(DISTINCT restaurant_bookmarks.restaurant_id)' )
				),
				'where' => array (
						'restaurant_bookmarks.user_id' => $userId,
                        'restaurant_bookmarks.type' => 'lo'
				)
		) );
       $test3=$resLo->toArray ();
        return array (
				'loved_it_count' =>(int) $test3[0]['total_count_lo'],
				'been_there_count' =>(int) $test1[0]['total_count_bt'],
				'crave_it_count' =>(int) $test2[0]['total_count_wl'] 
		);
	}
}//end of class
