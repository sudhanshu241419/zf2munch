<?php

namespace User\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Predicate\Expression;
use User\UserFunctions;
use User\Model\User;



class UserReferrals extends AbstractModel {
    
	public $user_id;
	public $inviter_id;
	public $order_placed;
	public $updated_on;
    public $mail_status;
    public $restaurant_id = 0;
	protected $_db_table_name = 'User\Model\DbTable\UserReferralsTable';
    
    public function __construct() {
		$this->updated_on = date ( "Y-m-d H:i:s" );
	}
    
    public function getReferralData($user_id){
        $select = new Select();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array(
            //'updated_on' => new Expression("DATE_FORMAT(`user_referrals`.`updated_on`, '%Y-%m-%d')"),
            'order_placed'=>'order_placed',
            )
        );
        $select->join(
            'users', 
            'users.id = user_referrals.user_id', 
            array(
                'name'=> new Expression('CONCAT(`first_name`," ",`last_name`)'),
                'id',
                'email',
                'display_pic_url',
                'created_at' => new Expression("DATE_FORMAT(`users`.`created_at`, '%d/%m/%Y')")
                ), 
            'inner');
        
        $where = new Where ();
		$where->equalTo ('user_referrals.inviter_id', $user_id);
        $select->where($where);
        $refDetails = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select);
        return $refDetails->toArray();
    }

    /**
     * Insert data in this table
     * How to Use                 
     * $ur = new \User\Model\UserReferrals();
     * $response = $ur->insert(array('user_id' => 544,'inviter_id' => 674,'updated_on' => date("Y-m-d H:i:s")));
     * @param array $data with keys user_id, inviter_id, order_placed, updated_on
     * @return array
     */
    public function insert($data){
        $response = array();
        $response['status'] = 'OK';

        try {
            if(! (isset($data['user_id']) && isset($data['inviter_id'])) ){
                throw new \Exception("Missing required parameters.");
            }
            $data['updated_on'] = date("Y-m-d H:i:s");
            /* @var $writeGateway \Zend\Db\TableGateway\TableGateway */
            $writeGateway = $this->getDbTable()->getWriteGateway();
            $affected_rows = $writeGateway->insert($data);
            if ($affected_rows > 0) {
                $response['result'] = TRUE;
            } else {
                $response['result'] = FALSE;
            }
        } catch (\Exception $e) {
            $response['status'] = 'FAIL';
            $response['result'] = array();
            $response['error'] = $e->getMessage();
        }
        return $response;
    }
    
    public function getUserReferralOrderPlacedCount($inviter_id){
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $options = array(
            'columns' => array('count' => new \Zend\Db\Sql\Expression("COUNT(*)") ),
            'where' => array('inviter_id' => $inviter_id, 'order_placed' => 1, 'ref_amt_credited' => 0)
        );
        
        //if user_has valid referral_code, return it
        $refDetail = $this->find($options)->toArray();
        return $refDetail[0]['count'];
    }
    
    public function getReferredUsersArr($inviter_id){
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $options = array(
            'columns' => array('user_id'),
            'where' => array('inviter_id' => $inviter_id)
        );
        
        $list = $this->find($options)->toArray();
        $result = array();
        foreach ($list as $i => $item) {
            $result[] = $item['user_id'];
        }
        return $result;
    }
    public function updateUserReferrals($id)
    {
        $writeGateway = $this->getDbTable()->getWriteGateway();
        $rowsAffected = $writeGateway->update(array(
            'order_placed' => 1,
            'mail_status' => 1
        ), array(
            'user_id' => $id,
            'order_placed' => 0
        ));
        if ($rowsAffected) {
            return true;
        } else {
            return false;
        }
    }
    public function getInviterId($id){
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $options = array(
            'columns' => array('inviter_id'),
            'where' => array('user_id' => $id)
        );
        $list = $this->find($options)->toArray();
        return $list[0]['inviter_id'];
    }
    public function userExist($id){
       $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $options = array(
            'columns' => array('count' => new \Zend\Db\Sql\Expression("COUNT(*)") ),
            'where' => array('user_id' => $id,'order_placed'=> 0)
        );
        //if user exist in referral table, return it
        $userDetail = $this->find($options)->toArray();
        return $userDetail[0]['count'];
    }

    
    /**
     * Get inviters with not redeemed placed order by invitees more than 2
     * @return array array of arrays of the form array([inviter_id] => 500,[count] => 6)
     */
    public function getInvitersThreeOrMoreOrderPlaced(){
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $options = array(
            'columns' => array('inviter_id','count' => new \Zend\Db\Sql\Expression("COUNT(*)") ),
            'where' => array('order_placed' => 1, 'ref_amt_credited' => 0),
            'group' => 'inviter_id'
        );
        return $this->find($options)->toArray();
    }
    
    
    public function updateThreeUsersRefAmtCredited($inviter_id){
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $options = array(
            'columns' => array('user_id', 'inviter_id'),
            'where' => array('order_placed' => 1, 'ref_amt_credited' => 0, 'inviter_id' => $inviter_id),
        );
        $users = $this->find($options)->toArray();
        
        $affected_user_ids = array();
        if(count($users) < 3){
            throw new \Exception('Invalid function call.');
        }
        
        for($i = 0; $i < 3; $i++){
            $update = new \Zend\Db\Sql\Update();
            $update->table($this->getDbTable()->getTableName());
            $where = new Where();
            $user_tobe_affected = $users[$i]['user_id'];
            $where->equalTo('user_id', $user_tobe_affected);
            $update->where($where);
            $update->set(array('ref_amt_credited' => 1, 'updated_on' => date('Y-m-d H:i:s')));
            //pr($update->getSqlString(),1);
            $this->getDbTable()->getWriteGateway()->updateWith($update);
            $affected_user_ids[] = $user_tobe_affected;
        }
        return $affected_user_ids;
    }
    public function FirstTransactionReferralUser($userId) {
        $transaction_table_data[] = array(
            'user_id' => $userId,
            'transaction_amount' => 5,
            'transaction_type' => 'credit',
            'category' => 2,
            'remark' => 'Credited $5 against First Referral Succesful Order',
        );
        if (!empty($transaction_table_data)) {
           $user_transactions = new \User\Model\UserTransactions();
           $user = new \User\Model\User();
            foreach ($transaction_table_data as $data) {
             $result = $user_transactions->insertDataUpdateWallet($data);
            }
            $result_wb =  $user->setUserWalleTBalance($userId); 
           return true; 
        }
        return false;
    }

    public function sendReferralMailUserInviter($userId,$restaurantId = false,$orderPoint=false)
    {
        $userFunctions = new UserFunctions ();
        $commonFunctions = new \MCommons\CommonFunctions();
        $restaurantServer = new RestaurantServer();
        $userOrder = new \User\Model\UserOrder();
        $userModel = new User();
        
        //Get Order detail
        $options = array('user_id'=>$userId,'restaurant_id'=>$restaurantId);
        $isAlreadyOrder = $userOrder->isAlreadyOrder($options);
        $totalOrder = count($isAlreadyOrder);
        
        $userexist = $this->userExist($userId);
        //Get Restaurant Server Details
        $restaurantServer->user_id = $userId;
        $restaurantServer->restaurant_id = $restaurantId;
        $userServerData = $restaurantServer->findExistingUser();
        
        if ($userexist == 1 && $totalOrder==1 && !empty($userServerData)) {
           $getinviterid = $this->getInviterId($userId);
           if (isset($getinviterid)) {
               $inviterData = $userModel->getUserDetail(array(
                   'columns' => array(
                       'inviteremail' => 'email',
                       'invitername' => 'first_name'
                   ),
                   'where' => array(
                       'id' => $getinviterid
                   )
               ));
               $userData = $userModel->getUserDetail(array(
                   'columns' => array(
                       'useremail' => 'email',
                       'username' => 'first_name'
                   ),
                   'where' => array(
                       'id' => $userId
                   )
               ));
               
               $feed = array(
                    'user_id' => $getinviterid,
                    'user_name' => ucfirst($inviterData ['invitername'])
                );
                $replacementData = array('user_name'=>ucfirst($inviterData ['invitername']));
                $otherReplacementData = array();
                $commonFunctions->addActivityFeed($feed, 62, $replacementData, $otherReplacementData);
           }
       }else{
           return false;
       }
    }
    
    /**
     * Returns number of total referred users with ref_amt_credited = 1
     * @param int $inviter_id
     * @return int 
     */
    public function getTotalReferredUsersWithAmountCredited($inviter_id){
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $options = array(
            'columns' => array('count' => new \Zend\Db\Sql\Expression("COUNT(*)") ),
            'where' => array('inviter_id' => $inviter_id, 'order_placed' => 1, 'ref_amt_credited' => 1)
        );
        
        //if user_has valid referral_code, return it
        $refDetail = $this->find($options)->toArray();
        return $refDetail[0]['count'];
    }
    
    public function getReferralDetails($userId,$restaurantId){
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $options = array(
            'columns' => array('user_id','inviter_id','order_placed'),
            'where' => array('user_id' => $userId,'restaurant_id'=>$restaurantId)
        );
        return $this->find($options)->toArray();        
    }
    
    public function updateReferralRestaurant($data){
        $writeGateway = $this->getDbTable()->getWriteGateway();
        $rowsAffected = $writeGateway->update($data, array(
            'user_id' => $this->user_id,
            'order_placed' => 0,            
        ));
        if ($rowsAffected) {
            return true;
        } else {
            return false;
        }
    }
    
    public function gettingCustomersWhoReferMostFriends($startDate,$endDate) {
        $select = new Select ();
        $select->from ( $this->getDbTable ()->getTableName () );
        $select->columns ( array (
            'inviter_id',
            'total_referals' => new Expression('COUNT(user_referrals.inviter_id)')
        ) );
        $select->join ( array (
				'rs' => 'restaurant_servers' 
		), 'user_referrals.inviter_id = rs.user_id and user_referrals.restaurant_id = rs.restaurant_id', array (
                    'restaurant_id',
                    'user_id',
                    'code'
		), $select::JOIN_INNER );
        $where = new Where ();
        //$where->equalTo('user_referrals.restaurant_id', 'rs.restaurant_id');
        $where->between('rs.date', $startDate, $endDate);
        $select->where ( $where );
        $select->group('user_referrals.inviter_id','user_referrals.restaurant_id');
        $select->order('total_referals desc');
        $select->limit(3)->offset(0);
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $serversList = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select )->toArray();
        return $serversList;
    }
    public function gettingServerReferals($code,$startDate,$endDate) {
        $select = new Select ();
        $select->from ( $this->getDbTable ()->getTableName () );
        $select->columns ( array (
            'inviter_id',
            'total_referals' => new Expression('COUNT(user_referrals.inviter_id)')
        ) );
        $select->join ( array (
				'rs' => 'restaurant_servers' 
		), 'user_referrals.inviter_id = rs.user_id', array (
                    'restaurant_id'
		), $select::JOIN_INNER );
        $select->join ( array (
				's' => 'servers' 
		), 'rs.code = s.code', array (
                    'server_name' => new Expression("CONCAT(s.first_name,' ',s.last_name)")
		), $select::JOIN_INNER );
        $where = new Where ();
        $where->equalTo('s.code', $code);
        $where->between('rs.date', $startDate, $endDate);
        $select->where ( $where );
        $select->group('inviter_id');
        $select->order('total_referals desc');
        $select->limit(1);
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $serversList = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select )->toArray();
        return $serversList;
    }
    
    public function getInviterReferralExist($userId,$restaurantId,$inviterId){
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $options = array(
            'columns' => array('user_id','inviter_id','order_placed'),
            'where' => array('user_id' => $userId,'restaurant_id'=>$restaurantId, 'inviter_id'=>$inviterId)
        );
        return $this->find($options)->toArray();        
    }
    public function getRestaurantsTotalReferrals($restId,$restStartDate,$restEndDate) {
       $select = new Select ();
        $select->from ( $this->getDbTable ()->getTableName () );
        $select->columns ( array (
            'total_referals' => new Expression('COUNT(user_referrals.inviter_id)')
        ) );
        $select->join ( array (
				'rs' => 'restaurant_servers' 
		), 'user_referrals.inviter_id = rs.user_id', array (
		), $select::JOIN_INNER );
        
        $where = new Where ();
        $where->equalTo('user_referrals.restaurant_id', $restId);
        $where->between('rs.date', $restStartDate, $restEndDate);
        $select->where ( $where );
        $select->group('inviter_id');
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $data = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select )->toArray();
        if (!empty($data)) {
            return $data[0]['total_referals'];
        }else{
            return 0;
        } 
        
    }
}