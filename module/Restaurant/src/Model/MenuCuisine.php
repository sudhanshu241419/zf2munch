<?php

namespace Restaurant\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;

class MenuCuisine extends AbstractModel {
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
	protected $_db_table_name = 'Restaurant\Model\DbTable\MenuCuisineTable';
	protected $_primary_key = 'id';
	
    public function getMenuIds($cuisineId=false){ 
       $options=array('columns' => array('Menu_id'),'where'=>array('Cuisine_id'=>$cuisineId)); 
       $this->getDbTable()->setArrayObjectPrototype('ArrayObject'); 
       $menus = $this->find($options)->toArray();
       return $menus;   
    }
}