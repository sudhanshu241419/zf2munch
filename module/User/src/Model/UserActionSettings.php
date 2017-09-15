<?php
namespace User\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;


class UserActionSettings extends AbstractModel
{

  public $id;
  public $user_id;
  public $order;
  public $reservation;
  public $bookmarks;
  public $checkin;
  public $muncher_unlocked;
  public $upload_photo;
  public $reviews;
  public $tips;
  public $email_sent;
  public $notification_sent;
  public $sms_sent;
  public $created_at;
  public $updated_at;

  protected $_db_table_name = 'User\Model\DbTable\UserActionSettingTable';

  protected $_primary_key = 'id';
   
    public function insert(){
        $data = $this->toArray();
        $writeGateway = $this->getDbTable ()->getWriteGateway ();      
		$rowsAffected = $writeGateway->insert ($data);
        if($rowsAffected){	
            $this->id = intval($writeGateway->getLastInsertValue());
            $data = $this->toArray();
            unset($data['updated_at'],$data['created_at']);
            return $data;
        }else{
            return false;
        }
	}
    
    public function update() {
        $data = $this->toArray();
        unset($data['created_at']);
       
		$writeGateway = $this->getDbTable ()->getWriteGateway ();
        $rowAffected = $writeGateway->update ( $data, array (
				'id' => $this->id 
		) );
                
		if($rowAffected){	
            unset($data['updated_at']);
            return $data;
        }else{
            return false;
        }
	}
    
    public function select(array $options=array()){
		$this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        return $this->find($options)->toArray();
	}
   
}