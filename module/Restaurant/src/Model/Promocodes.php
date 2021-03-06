<?php

namespace Restaurant\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;

class Promocodes extends AbstractModel {
	public $id;
	public $start_on;
	public $end_date;
    public $promo_code;
	public $discount;
	public $discount_type;
	public $status;
	public $minimum_order_amount;
	public $slots;
	public $days;
	public $deal_for;
	public $title;
	public $description;
    public $budget;
    public $promocodeType;
	protected $_db_table_name = 'Restaurant\Model\DbTable\PromocodesTable';
	protected $_primary_key = 'id';
    
    public function insert($data){
        $writeGateway = $this->getDbTable ()->getWriteGateway ();        
        $rowsAffected = $writeGateway->insert ($data);              
		
        if($rowsAffected){	
            $this->id = $writeGateway->getAdapter ()->getDriver ()->getLastGeneratedValue ();
            return true;
        }else{
            return false;
        }
	}
     public function update($data){        
        $writeGateway = $this->getDbTable()->getWriteGateway();
        $rowsAffected = $writeGateway->update($data, array(
            'id' => $this->id
        ));
        if($rowsAffected){
            return true;
        }else{
            return false;
        }
    }
    
    public function updateCron($id=false){
        $this->getDbTable ()->getWriteGateway ()->update ( array('cronUpdate'=>1), array (
				'id' => $id
		) );
		return true;
    }
    
    public function getPromocodeDetails($options){
        $select = new Select ();
        $select->from ( $this->getDbTable ()->getTableName () );
        $select->columns ( array (
            'id',
            'start_on',
            'end_date',
            'promo_code',
            'budget',
            'promocodeType',
            'discount',
            'discount_type',
            'status',
            'minimum_order_amount',
            'slots',
            'days',
            'deal_for',
            'title',
            'description',
        ) );
        $select->where ( array (
        'promo_code' => $options['promocode'],
        'restaurant_id'=>$options['restaurant_id'],
                                   
        ) );
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $promocodeDetails = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select )->toArray();
       
        if(!empty($promocodeDetails)){
            return $promocodeDetails;
        }else{
            return false;
        }
    }
    public function getMaRestaurantPromocode($options){
        $select = new Select ();
        $select->from ( $this->getDbTable ()->getTableName () );
        $select->columns ( array (
            'id',
            'start_on',
            'end_date',
            'promo_code',
            'discount',
            'discount_type',
            'status',
            'minimum_order_amount',
            'slots',
            'days',
            'deal_for',
            'title',
            'description',
        ) );
        $select->where ( array (        
        'restaurant_id'=>$options['restaurant_id'],
        'status'=>1,                            
        ) );
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $promocodeDetails = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select )->toArray();
       
        if(!empty($promocodeDetails)){
            return $promocodeDetails;
        }else{
            return false;
        }
    }
}

