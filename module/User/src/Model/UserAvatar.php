<?php
namespace User\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;


class UserAvatar extends AbstractModel
{

    public $id;
       
    public $user_id;

    public $avatar_id;
    
    public $action_count;
    
    public $date_earned;
    
    public $total_earned;
    
    public $status;

    protected $_db_table_name = 'User\Model\DbTable\UserAvatarTable';

    protected $_primary_key = 'id';
   
    public function insert($data){
        $writeGateway = $this->getDbTable ()->getWriteGateway ();
        if($this->id){
            $rowsAffected = $writeGateway->update ($data,array('id' => $this->id));
        }else{
            $rowsAffected = $writeGateway->insert ($data);
        }        
		
        if($rowsAffected){		
            return true;
        }else{
            return false;
        }
	}
   
}