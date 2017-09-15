<?php

namespace Cuisine\Model;

use MCommons\Model\AbstractModel;

class Cuisine extends AbstractModel {
	public $id;
	public $cuisine;
	public $cuisine_type;
	public $description;
	public $image_name;
	public $created_on;
	public $search_status;
	public $status;
	public $priority;
	protected $_db_table_name = 'Cuisine\Model\DbTable\CuisineTable';
	protected $_primary_key = 'id';
	public function getAllCuisine(array $options = array()) {
		$this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' );
		$cuisines = $this->find ( $options )->toArray ();
		return $cuisines;
	}
    
    public function getCuisine($cuisId=0){
        $options = array (
				'columns' => array (
						'cuisine'
				),
				'where' => array (
						'id' => $cuisId 
				) 
		);
        $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' );
		$cuisines = $this->find ( $options )->current ();
		return $cuisines['cuisine'];
    }
}