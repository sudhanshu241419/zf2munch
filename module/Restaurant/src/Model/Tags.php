<?php

namespace Restaurant\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
class Tags extends AbstractModel {
	public $id;
	public $name;
	public $status;
	protected $_db_table_name = 'Restaurant\Model\DbTable\TagsTable';
	protected $_primary_key = 'id';
	public function getSweestakesTagsRestaurant() {
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$select->columns ( array (
				'tags_id'=>'id',
                'tag_name'=>'name'
		) );
				
		$select->join ( array (
				'rt' => 'restaurant_tags' 
		), 'tags.id = rt.tag_id', array (
				'restaurant_id' 
		), $select::JOIN_INNER );
		
		$select->join ( array (
				'r' => 'restaurants' 
		), 'r.id = rt.restaurant_id', array (
				'restaurant_name',
				'rest_code',
				'address',
				'landmark',
				'restaurant_image_name',                
                'delivery',
                'takeout',
                'reservations',
                'menu_without_price',
                'accept_cc_phone'
				
		), $select::JOIN_INNER );	
		
		$select->where ( array (
				'tags.name' => 'sweepstakes',
                'tags.status'=>1,
                'rt.status'=>1,                
		) );
		//var_dump($select->getSqlString($this->getPlatform('READ')));die;
		$sweepsTakesRestaurant = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select )->toArray();
		return $sweepsTakesRestaurant;
	}
        public function getTagDetailByName($tagName){
            $select = new Select ();
                    $select->from ( $this->getDbTable ()->getTableName () );
                    $select->columns ( array (
                    'tags_id'=>'id',
                    'tag_name'=>'name'
                    ) );
                    $select->where ( array (
                    'name' => $tagName,
                    'status'=>1,                            
                    ) );
                    //var_dump($select->getSqlString($this->getPlatform('READ')));die;
                    $tagDetails = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select )->toArray();
                    return $tagDetails;
        }
}

