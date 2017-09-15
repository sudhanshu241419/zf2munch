<?php

namespace User\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;

class CheckinImages extends AbstractModel {
	public $id;
	public $checkin_id;
	public $image;
	public $image_path;
	public $status;
    public $sweepstakes_status_winner;
	
	protected $_db_table_name = 'User\Model\DbTable\CheckinImagesTable';
	protected $_primary_key = 'id';
    
    public function insert($data){
        $writeGateway = $this->getDbTable ()->getWriteGateway ();
        $rowsAffected = $writeGateway->insert ($data);
        $lastInsertId = $writeGateway->getAdapter ()->getDriver ()->getLastGeneratedValue ();
        return $lastInsertId;
    }     
    
    public function delete(){
        $writeGateway = $this->getDbTable ()->getWriteGateway ();
        $rowsAffected = $writeGateway->delete ( array ('id' => $this->id) );
        return $rowsAffected;
    }
    
    public function checkinTotalImages($userId){ 
        $select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$select->columns ( array (
                'images'=>new \Zend\Db\Sql\Expression('count(checkin_images.id)'),
        ) );
		$select->join ( array (
				'cek' => 'user_checkin' 
		), 'checkin_id=cek.id',array(), $select::JOIN_INNER );
		$select->where ( array (
				'cek.user_id' => $userId 
		) );
        //pr($select->getSqlString($this->getPlatform('READ')),true);
        $userEatingHabitDetails = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select );
		return $userEatingHabitDetails->toArray ();
    }
    
    public function findSweepstakesImage($userId,$restId,$campaignsData){
        $select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$select->columns ( array (
                'images'=>new \Zend\Db\Sql\Expression('count(checkin_images.id)'),
        ) );
		$select->join ( array (
				'cek' => 'user_checkin' 
		), 'checkin_id=cek.id',array(), $select::JOIN_INNER );
		$select->where ( array (
				'cek.user_id' => $userId,
                'cek.restaurant_id'=>$restId
		) );
        $select->where->between('cek.created_at', $campaignsData[0]['start_on'], $campaignsData[0]['end_date']);
        //pr($select->getSqlString($this->getPlatform('READ')),true);
        $swi = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select )->toArray();
		return $swi[0]['images'];
    }
}
