<?php

namespace Restaurant\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;

class CuratedList extends AbstractModel {
	public $id;
	public $phrase;
	public $restaurant_ids;
	public $food_items;
	public $cuisine;
	public $type_of_place;
	public $curated_image;
	public $curated_video;
	public $status;
	
	protected $_db_table_name = 'Restaurant\Model\DbTable\CuratedListTable';
	protected $_primary_key = 'id';
	public function getCuratedList(array $options = array()) {
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$select->columns ($options['columns']);	
		$select->where ($options['where']);
		$curatedList = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select )->toArray();
        return $this->formatCuratedList($curatedList);        
	}
    
    public function formatCuratedList($curatedList){
        $cl = array();
        if(!empty($curatedList)){
            foreach($curatedList as $key =>$val){
                $cl[$key]['curated_id']=$val['id'];
                $cl[$key]['cuisine'] = explode(",", $val['cuisine']);
                $cl[$key]['phrase'] = $val['phrase'];
                $cl[$key]['type_of_place'] = explode(",", $val['type_of_place']);                             
                $cl[$key]['image'] = (isset($val['curated_image']) && !empty($val['curated_image']))?"munch_videos/curated_files/".$val['id'].$val['curated_image']:'';
                $cl[$key]['video'] = (isset($val['curated_video']) && !empty($val['curated_video']))?"munch_videos/curated_files/".$val['id'].$val['curated_video']:'';           
                //$cl[$key]['food_items'] = explode(",", $val['food_items']);  
            }
        }
        return $cl;
    }
}