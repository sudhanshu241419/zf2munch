<?php

namespace Restaurant\Model;

use MCommons\Model\AbstractModel;

class MasterCuisines extends AbstractModel {
	public $id;
	public $cuisine;
	public $cuisine_type;
	public $description;
	public $image_name;
	public $created_on;
	public $search_status;
	public $status;
	public $priority;
	const STATUS_INACTIVE = '0';
	const STATUS_ACTIVE = '1';
	protected $_db_table_name = 'Restaurant\Model\DbTable\MasterCuisineTable';
	protected $_primary_key = 'id';

    public function getCuisineId($menuType=false){
       $options=array('columns' => array('id'),'where'=>array('status'=>'1','cuisine'=>$menuType)); 
       $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
       $cuisines = $this->find($options)->current();
       return $cuisines;
    }
}