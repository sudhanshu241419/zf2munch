<?php
namespace User\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;


class UserSocialMediaDetails extends AbstractModel
{

    public $id;

    public $user_id;
    
    public $user_source;   

    public $user_name;

    public $access_token;

    protected $_db_table_name = 'User\Model\DbTable\UserSocialMediaDetailTable';

    protected $_primary_key = 'id';
   
    public function insert($data){
		$writeGateway = $this->getDbTable ()->getWriteGateway ();
		$rowsAffected = $writeGateway->insert ($data);
		$lastInsertId = $writeGateway->getAdapter ()->getDriver ()->getLastGeneratedValue ();
		return $lastInsertId;
	}
    
    public function update($data){  
        $writeGateway = $this->getDbTable ()->getWriteGateway ();
        $rowsAffected = $writeGateway->update($data, array(
                'id' => $this->id
            ));
        if($rowsAffected){
            return true;
        }else{
            return false;
        }
        
    }
   
}