<?php
namespace User\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;


class EmailSubscription extends AbstractModel
{

    public $id;

    public $email;

    public $source;

    public $status;

    public $created_on;

    public $comment;
    
    public $zip;

    protected $_db_table_name = 'User\Model\DbTable\EmailSubscriptionTable';

    protected $_primary_key = 'id';
   
    public function insert($data){
		$writeGateway = $this->getDbTable ()->getWriteGateway ();      
		$rowsAffected = $writeGateway->insert ($data);
        if($rowsAffected){
            $this->id = $writeGateway->getAdapter ()->getDriver ()->getLastGeneratedValue ();
            return true;
        }else{
            return false;
        }
	}
   
}