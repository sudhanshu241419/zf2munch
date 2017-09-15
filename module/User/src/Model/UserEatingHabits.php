<?php

namespace User\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;

class UserEatingHabits extends AbstractModel {
	public $id;	
    
	public $user_id;

	public $favorite_beverage;	

	public $where_do_you_go;

	public $comfort_food;

	public $favorite_food;	

	public $dinner_with;

	public $created_on;

	public $updated_on;
    
	protected $_db_table_name = 'User\Model\DbTable\UserEatingHabitsTable';
	protected $_primary_key = 'id';
	public function findUserEatingHabits($user_id = 0) {
		$data = array ();
		$result = $this->find ( array (
				'where' => array (
						'user_id' => $user_id 
				) 
		) )->current ();
		
		return $result;
	}	
	public function update($data)
    {
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
	
	public function insert($data){
		$writeGateway = $this->getDbTable ()->getWriteGateway ();
		$rowsAffected = $writeGateway->insert ($data);
		$lastInsertId = $writeGateway->getAdapter ()->getDriver ()->getLastGeneratedValue ();
		return $lastInsertId;
	}
      
}
