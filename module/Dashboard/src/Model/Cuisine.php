<?php

namespace Dashboard\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Where;


class Cuisine extends AbstractModel {
	
	public $id;
	
	protected $_db_table_name = 'Dashboard\Model\DbTable\CuisineTable';
    
    public static $cusine_in_order = array('Americas' => 0,'African' => 1, 'Asia' => 2, 'Europe' => 3, 'Africa' => 4, 'Australia' => 5);
    
	public function get_all_cuisine_by_type(){
       $select = new Select ();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array(
            '*'
        ));
        $select->where(array(
            'status' =>'1'
        ));
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $records = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->toArray();  
       return $records;
  }
  
  public function getRestaurantCuisine($resId=0) {
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$select->columns ( array (
				'cuisine' 
		) );
		
		$select->join ( array (
				'rc' => 'restaurant_cuisines' 
		), 'cuisines.id = rc.cuisine_id', array (), $select::JOIN_INNER );
		$select->where ( array (
				'rc.restaurant_id' => $resId,
				'rc.status' =>'1'
		) );
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
		$cuisines = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select )->toArray();
        return $cuisines;
	}

}