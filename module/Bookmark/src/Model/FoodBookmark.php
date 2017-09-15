<?php

namespace Bookmark\Model;

use MCommons\Model\AbstractModel;
use Restaurant\Model\Menu;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use MCommons\StaticOptions;

class FoodBookmark extends AbstractModel {
	public $id;
	public $menu_id;
	public $restaurant_id;
	public $menu_name;
	public $user_id;
	public $type;
	public $created_on;
	protected $_db_table_name = 'Bookmark\Model\DbTable\FoodBookmarkTable';
	protected $_primary_key = 'id';
	public function getFoodBookmarkCount(array $options = array()) {
		$this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' );
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$select->columns ( array (
				'total_count' => new Expression ( 'COUNT(id)' ),
				'type' 
		) );
		
		$select->where ( array (
				'restaurant_id' => $options ['restaurant_id'],
				'menu_id' => $options ['menu_id'] 
		) );
		$select->group ( 'type' );
		$response = $this->getDbTable ()->getReadGateway ()->selectWith ( $select );
		return $response->toArray ();
	}
	public function addBookmark() {	
		$data ['menu_id'] = $this->menu_id;
		$data ['type'] = $this->type;
		$data ['created_on'] = $this->created_on;
		$data ['user_id'] = $this->user_id;
		$data ['restaurant_id'] = $this->restaurant_id;
		$data['menu_name'] = $this->menu_name;
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
		return false;
	}
	public function addMenuBookMark() {
		$options = array (
				'columns' => array (
						'menu_id' 
				),
				'where' => array (
						'menu_id' => $this->menu_id,
						'user_id' => $this->user_id,
						'type' => $this->type 
				) 
		);
		
		$is_exist_bookmark = $this->find ( $options )->current ();
		if (! empty ( $is_exist_bookmark )) {
			return false;
		}
		$menuObj = new Menu ();
		$menuDetail = $menuObj->getMenuDetail ( $this->menu_id );
		if (empty ( $menuDetail )) {
			return false;
		}
		$writeGateway = $this->getDbTable ()->getWriteGateway ();
		
		$rowsAffected = 0;
		
		if ($this->user_id) {
			$data ['menu_id'] = $this->menu_id;
			$data ['type'] = $this->type;
			$data ['created_on'] = $this->created_on;
			$data ['user_id'] = $this->user_id;
			$this->restaurant_id = $menuDetail ['restaurant_id'];
			$data ['restaurant_id'] = $menuDetail ['restaurant_id'];
			$data ['menu_name'] = $menuDetail ['item_name'];
			$this->menu_name = $menuDetail ['item_name'];
			$data = array_intersect_key ( $data, array_flip ( array (
					'menu_id',
					'user_id',
					'type',
					'menu_name',
					'restaurant_id',
					'created_on' 
			) ) );
			$rowsAffected = $writeGateway->insert ( $data );
		}
		// Get the last insert id and update the model accordingly
		$lastInsertId = $writeGateway->getAdapter ()->getDriver ()->getLastGeneratedValue ();
		
		if ($rowsAffected >= 1) {
			$this->id = $lastInsertId;
			$response = $this->toArray ();
			$response = array_intersect_key ( $response, array_flip ( array (
					'id',
					'menu_id',
					'user_id',
					'type',
					'menu_name',
					'restaurant_id' 
			) ) );
			return $response;
		}
		return false;
	}
        public function getMenuBookmarkCount($restaurant_id = 0, $menu_id = 0) {
                $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' );
                $res = $this->find ( array (
                                'columns' => array (
                                                'total_count' => new Expression ( 'COUNT(menu_id)' ),
                                                'type' 
                                ),
                                'where' => array (
                                                'restaurant_id' => $restaurant_id,
                                                'menu_id' => $menu_id 
                                ),
                                'group' => new Expression ( 'type' ) 
                ) );
                return $res->toArray ();
        }
	public function getPersonListOfLoveitItem($menuId = 0) {
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$select->columns ( array () );
		$select->join ( array (
				'u' => 'users' 
		), 'u.id =  menu_bookmarks.user_id', array (
				'first_name',
				'last_name',
				'email',
				'display_pic_url' => new Expression ( 'if(u.display_pic_url is NULL,"",u.display_pic_url)' ) 
		), $select::JOIN_LEFT );
		$select->where ( array (
				'menu_bookmarks.menu_id' => $menuId,
				'menu_bookmarks.type' => $this->type 
		) );
		
		$personListOfLoveitItem = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select )->toArray ();
		return $personListOfLoveitItem;
	}
	public function checkIfUserBookmarked() {
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
     public function getMenuBookmarkCountOfType($restaurant_id = 0, $menu_id = 0, $type=false) {
        $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' );
        if($type){
            $res = $this->find(array(
            'columns' => array(
                'total_count' => new Expression('COUNT(menu_id)')                
            ),
            'where' => array(
                'restaurant_id' => $restaurant_id,
                'menu_id' => $menu_id,
                'type'=>$type
            ),

            ));
        }
       return $res->toArray ();         
    }
    public function getRestaurantBookmarkCountOfType($restaurant_id = 0, $type=false) {
        $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' );
        if($type){
            $res = $this->find(array(
            'columns' => array(
                'total_count' => new Expression('COUNT(menu_id)')                
            ),
            'where' => array(
                'restaurant_id' => $restaurant_id,                
                'type'=>$type
            ),

            ));
        }
       return $res->toArray ();         
    }
}//end of class