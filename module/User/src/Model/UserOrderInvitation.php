<?php
namespace User\Model;

use MCommons\Model\AbstractModel;

class UserOrderInvitation extends AbstractModel
{
    public $id;

    public $user_id;

    public $from_id;

    public $restaurant_id;

    public $message;

    public $msg_status;

    public $pre_order_id;

    public $order_token;

    public $friend_email;

    public $pay_anyone;
    
    public $user_type;
    
    public $created_on;
    
    protected $_db_table_name = 'User\Model\DbTable\UserOrderInvitationTable';
    
    protected $_primary_key = 'id';
    
    protected $_foreign_key = 'user_id';    
    
    
    
    
    public function getAllInvitation(array $options = array()){
		$this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' );
		$invitation = $this->find ( $options )->toArray ();
		return $invitation; 
    }
}