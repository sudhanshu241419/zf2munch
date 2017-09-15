<?php
namespace User\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;


class UserTip extends AbstractModel
{

    public $id;

    public $user_id;

    public $restaurant_id;

    public $status;

    public $created_at;

    public $tip;

    protected $_db_table_name = 'User\Model\DbTable\UserTipTable';

    protected $_primary_key = 'id';
   
    public function insert($data){
		$writeGateway = $this->getDbTable ()->getWriteGateway ();
		$rowsAffected = $writeGateway->insert ($data);
		$lastInsertId = $writeGateway->getAdapter ()->getDriver ()->getLastGeneratedValue ();
		return $lastInsertId;
	}
    
    public function getTipActivity($restaurantId,$userId,$bookmarkType){
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$select->columns ( array (
				'user_id'
		) );
		$select->where ( array (
				'restaurant_id' => $restaurantId,
				'user_id' => $userId,				
		) );
		
		$userTip = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select );
		//echo $select->getSqlString($this->getPlatform());
		return $userTip->toArray();
	}
    
    public function getUserTotalTip($userId) {
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$select->columns ( array (
				'total_tip' => new \Zend\Db\Sql\Expression ( 'COUNT(user_tips.id)' )
		) );
		$select->where ( array (
				'user_tips.user_id' => $userId,
                'user_tips.status' => array(0, 1)
		) );
		
		$totalTip = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select )->current();
		//pr($select->getSqlString($this->getPlatform('READ')),true);
        return $totalTip;
	}
    
    public function getUserTotalTipForDetails($userId) {
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$select->columns ( array (
				'total_tip' => new \Zend\Db\Sql\Expression ( 'COUNT(user_tips.id)' )
		) );
		$select->where ( array (
				'user_tips.user_id' => $userId
		) );
		
		$totalTip = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select )->current();
		//pr($select->getSqlString($this->getPlatform('READ')),true);
        return $totalTip;
	}
    
    public function getUserAllTip($userId=false)
    {
        $select = new Select();
		$select->from($this->getDbTable()->getTableName());
		$select->columns(array('id','restaurant_id'));
        $select->where(array('user_id' => $userId,'assignMuncher'=>'0','status'=>'1'));
        $select->where->notEqualTo('tip','');
		$totalCheckin = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select);
		return $totalCheckin->toArray();
    }
    public function updateMuncher($data){
        $this->getDbTable ()->getWriteGateway ()->update ( $data, array (
				'id' => $this->id
		) );
		return true;
    }
   public function restaurantTotalTips($restaurantId){
       $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' );
       $res = $this->find ( array (
				'columns' => array (
						'total_count' => new Expression ( 'COUNT(id)' ),	
				),
				'where' => array (
						'restaurant_id' => $restaurantId,
                        'status' => 1
				)
	
		) );
		return $res->toArray()[0];
   }
   
   public function updateCronOrder($id=false){
        $this->getDbTable ()->getWriteGateway ()->update ( array('cronUpdate'=>1), array (
				'id' => $id
		) );
		return true;
    }
}