<?php
namespace User\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;


class Avatar extends AbstractModel
{

    public $id;
       
    public $avatar;

    public $name;
    
    public $type;
    
    public $avtar_image;
    
    public $message;
    
    public $temp_message;
    
    public $action;
    
    public $action_number;
    
    public $status;

    protected $_db_table_name = 'User\Model\DbTable\AvatarTypeTable';

    protected $_primary_key = 'id';
   
    public function insert($data){
        $writeGateway = $this->getDbTable ()->getWriteGateway ();      
		$rowsAffected = $writeGateway->insert ($data);
        if($rowsAffected){		
            return true;
        }else{
            return false;
        }
	}
   
}