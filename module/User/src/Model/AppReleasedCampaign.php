<?php
namespace User\Model;
use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;


class AppReleasedCampaign extends AbstractModel
{
    public $id;

    public $phone;
    
    public $email;

    protected $_db_table_name = 'User\Model\DbTable\AppReleasedCampaignTable';

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