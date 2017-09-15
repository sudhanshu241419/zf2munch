<?php

namespace Restaurant\Model;

use MCommons\Model\AbstractModel;
use MCommons\StaticOptions;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Where;
use User\UserFunctions;

class MenuBookmark extends AbstractModel {
	public $id;
	public $menu_id;
	public $restaurant_id;
	public $menu_name;
	public $user_id;
	public $type;
	public $created_on;
	const LOVEIT = 'lo';
	const TRIEDIT = 'ti';
	const WANTIT = 'wi';
	protected $_db_table_name = 'Restaurant\Model\DbTable\MenuBookmarkTable';
	protected $_primary_key = 'id';
	public function menuBookmarksCounts(array $options = array()) {
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$select->columns ( array (
				'total_count' => new Expression ( 'COUNT(menu_id)' ),
				'type' 
		) );
		$select->where ( array (
				'menu_id' => $options ['columns'] ['menu_id'] 
		) );
		
		$select->group ( array (
				'type' 
		) );
		
		$menubookmarks = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select );
		//echo $select->getSqlString($this->getPlatform());
		return $menubookmarks;
	}
	public function getMenuBookmarksByUserId($menu_id, $user_id) {
		$select = new Select ();
		$bookmarkType = StaticOptions::$book_mark_types;
		$bkmark_detail = array ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$select->columns ( array (
				'total_count' => new Expression ( 'COUNT(menu_id)' ),
				'type' 
		) );
		$select->where ( array (
				'menu_id' => $menu_id,
				'user_id' => $user_id 
		) );
		
		$select->group ( array (
				'type' 
		) );
		
		$menubookmarks = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select )->toArray ();
		
		if (! empty ( $menubookmarks )) {
			foreach ( $menubookmarks as $b ) {
				$key = $b ['type'];
				$bkmark_detail [$key] = $b ['total_count'];
			}
			
			foreach ( $bookmarkType as $type ) {
				if (! array_key_exists ( $type, $bkmark_detail ))
					$bkmark_detail [$type] = 0;
			}
			
			return $bkmark_detail;
		} else {
			return array (
					self::LOVEIT => 0,
					self::TRIEDIT => 0,
					self::WANTIT => 0 
			);
		}
	}
	
	public function getMenuBookmarkActivity($restaurantId,$userId,$bookmarkType){
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$select->columns ( array (
				'user_id'
		) );
		$select->where ( array (
				'restaurant_id' => $restaurantId,
				'user_id' => $userId,
				'type'=> $bookmarkType
		) );
		
		$menubookmarks = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select );
		//echo $select->getSqlString($this->getPlatform());
		return $menubookmarks->toArray();
	}
	public function getUserFoodBookmarkCount($userId)
	{
		$select = new Select();
		$select->from($this->getDbTable()
				->getTableName());
		$select->columns(array(
	
				'total_menu_bookmark' => new Expression('COUNT(id)')
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
	public function createBookmark($data){
		$writeGateway = $this->getDbTable()->getWriteGateway();
		$writeGateway->insert($data);
		return true;
	}
	public function getUserMenuesBookmarkCount($userId)
	{
		$countMenuBookmark=0;
		$userFunctions = new UserFunctions();
		$select = new Select();
		$select->from($this->getDbTable()
				->getTableName());
		
		$select->join ( array (
				'rs' => 'restaurants'
		), 'rs.id =  menu_bookmarks.restaurant_id', array (
				'restaurant_name'
		), $select::JOIN_INNER );
		$select->join ( array (
				'r' => 'menus'
		), 'r.id =  menu_bookmarks.menu_id', array (
				'status'
		), $select::JOIN_INNER );
		
		$where = new Where();
		$where->equalTo('user_id', $userId);
        $select->where($where);
        $select->group('menu_bookmarks.menu_id');
        //pr($select->getSqlString($this->getPlatform()),true);
		$totalbookmark = $this->getDbTable()
		->setArrayObjectPrototype('ArrayObject')
		->getReadGateway()
		->selectWith($select)
		->toArray();
		
		$response = $userFunctions->arrangeMenuBookmarks($totalbookmark);
		$response = $userFunctions->getCounts ( $response );
		$countMenuBookmark = $response['loved_it_count']+$response['want_it_count']+$response['tried_it_count'];
		return $countMenuBookmark;
	}
    public function getMenuSocialProofing($menuId,$userId,$bookmarkType){
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$select->columns ( array (
				'user_id'
		) );
		$select->where ( array (
				'menu_id' => $menuId,
				'user_id' => $userId,
				'type'=> $bookmarkType
		) );
		
		$menubookmarks = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select );
		//echo $select->getSqlString($this->getPlatform());
		return $menubookmarks->toArray();
	}

}
