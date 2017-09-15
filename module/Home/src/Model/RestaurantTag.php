<?php

namespace Home\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;

class RestaurantTag extends AbstractModel {
	public $id;
	public $restaurant_id;
	public $city_id;
	public $tag;
	
	protected $_db_table_name = 'Home\Model\DbTable\RestaurantTagTable';
	public function getPopularTags($cityId) {
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );		
		$select->columns ( array (
		    new Expression ( 'DISTINCT(`tag`) as tag' ) 
		) );				
		$select->where ( array (
				'city_id' => $cityId,				
		) );
//		var_dump($select->getSqlString($this->getPlatform('READ')));
//                die;
		$popularTagsDetail = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select );
		return $popularTagsDetail;
	}
    
    public function hasTags($rest_id) {
        $select = new Select ();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array('id'));
        $select->where(array('restaurant_id' => $rest_id, 'status' => 1));
        $tags = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select);
        if($tags->count() > 0){
            return true;
        }
        
        $restaurant = new \Restaurant\Model\Restaurant();
        if($restaurant->isAcceptCcPhoneEnabled($rest_id)){
            return true;
        }
        
        return false;
    }

    /**
     * get list of tags for a given restaurant
     * @param int $rest_id
     * @return array list of tags
     */
    public function getTags($rest_id) {
        $select = new Select();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array('id'));
        $select->join('tags', 'tags.id = restaurant_tags.tag_id', ['name']);
        $select->where(array('restaurant_tags.restaurant_id' => $rest_id, 'restaurant_tags.status' => 1));
        $rawTags = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->toArray(); 
        $tags = [];
        foreach ($rawTags as $tag) {
           $tags[] =  $tag['name'];
        }
        return $tags;
    }
}