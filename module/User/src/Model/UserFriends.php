<?php

namespace User\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;
use User\UserFunctions;
use MCommons\CommonFunctions;


class UserFriends extends AbstractModel {
	public $id;
	public $user_id;
	public $to_id;
	public $restaurant_id;
	public $message;
	public $msg_status;
	public $reservation_id;
	public $friend_email;
	public $user_type;
	public $created_on;
	protected $_db_table_name = 'User\Model\DbTable\UserFriendsTable';
	protected $_primary_key = 'id';
	/**
	 * Get User Friends List
	 *
	 * @param unknown $userId        	
	 * @param unknown $orderby        	
	 * @return Array
	 */
	public function getUserFriendList($userId, $orderby) {
		$order = 'first_name ASC';
		if ($orderby == 'name') {
			$order = 'first_name ASC';
		}elseif ($orderby == 'email') {
			$order = 'email ASC';
		}else{
			$order = 'user.created_at DESC';
		}
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$select->columns ( array (
				'friends_on'=>'created_on', 
                                'friend_id','user_id'
		) );
		$select->join ( array (
				'user' => 'users' 
		), 'user.id =  user_friends.friend_id', array (
                'first_name',
				'last_name',
				'email',
				'display_pic_url',
                'created_at',
                'city_id'
				
		), $select::JOIN_INNER );
		$where = new Where ();
		$where->equalTo ( 'user_friends.user_id', $userId );
		$where->equalTo ( 'user_friends.status', 1 );
		$select->where ( $where );
		$select->order ( $order );
        $select->group('friend_id');
		$friends = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select )->toArray ();
		return $friends;
	}
	/**
	 * Get User Total No Of Friends
	 *
	 * @param unknown $userId        	
	 * @return \ArrayObject
	 */
	public function getTotalFriends($userId) {
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$select->columns ( array (
				
				'total_friends' => new Expression ( 'COUNT(id)' ) 
		) );
		$where = new Where ();
		$where->equalTo ( 'user_id', $userId );
		$where->equalTo ( 'status', 1 );
		$where->notEqualTo ( 'friend_id', '' );
		$where->notEqualTo ( 'friend_id', 0 );
		$select->where ( $where );
		$totalFriends = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select )->current ();
		return $totalFriends;
	}
	public function unFriend($id, $userId, $unFrinds) {
        $data = array (
				'status' => $unFrinds 
		);
		
		$writeGateway = $this->getDbTable ()->getWriteGateway ();
		
		$dataUpdated = $writeGateway->update ( $data, array (
				'user_id' => $userId,
				'friend_id' => $id 
		) );
		
		$dataUpdated2 = $writeGateway->update ( $data, array (
				'user_id' => $id,
				'friend_id' => $userId 
		) );
		
		if ($dataUpdated > 0 && $dataUpdated2 > 0) {
			return true;
		} else {
			return false;
		}
	}
	public function getFriendStatus(array $options = array()) {
		$this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' );
		return $this->find ( $options )->current ();
	}
	public function createFriends($data,$userId = false) {
            	$userFunctions = new UserFunctions();
		if (! empty ( $data )) {
			$userFriends = new UserFriends ();
			$userFriends->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' );
			$options = array (
					'columns' => array (
							'count' => new \Zend\Db\Sql\Expression ( 'COUNT(*)' ) 
					),
					'where' => array (
							'user_id' => $data ['user_id'],
							'friend_id' => $data ['friend_id'],
							'status' => array(1,2) 
					) 
			);
			$ifFriendship = $userFriends->find ( $options )->current ()->getArrayCopy ();
            if ($ifFriendship ['count'] == 0) {
                $userId = $data ['user_id'];
                //Below functionality commented as intruction of sudhanshu sir bug:38687
//				if($userId){
//					$points = $userFunctions->getAllocatedPoints ( 'madeFriend' );
//					$message = 'Friend Accept Invitation! You\'ll need points to have the most fun, here take 15. Hoard them wisely.';
//					$userFunctions->givePoints($points, $userId,$message,null,true);
//				}
				$dataArray = array (
						'user_id' => $data ['user_id'],
						'friend_id' => $data ['friend_id'],
                        'invitation_id' => $data ['invitation_id'],
						'created_on' => $data ['created_on'],
						'status' => $data ['status'] 
				);
				$writeGateway = $this->getDbTable ()->getWriteGateway ();
				$rowsAffected = $writeGateway->insert ( $dataArray );
            }else{
                $invitee=array('status'=>$data ['status'],'invitation_id' => $data ['invitation_id']);
                $this->getDbTable ()->getWriteGateway ()->update ( $invitee, array (
				'user_id' => $data ['user_id'],'friend_id' => $data ['friend_id']
                ) );
                $this->getDbTable ()->getWriteGateway ()->update ( $invitee, array (
				'friend_id' => $data ['user_id'],'user_id' => $data ['friend_id']
                ) );
            }
			return true;
		}
	}
	public function getCommonFriends($userId, $currentUserId) {
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$select->columns ( array (
				'friend_id',
				'cnt' => new Expression ( 'COUNT(*)' ) 
		) );
		$where = new Where ();
		$where->IN ( 'user_id', array (
				$userId,
				$currentUserId 
		) );
		$where->equalTo ( 'status', 1 );
		$select->where ( $where );
		$select->group ( 'friend_id' );
		$select->having ( 'cnt=2' );
		// var_dump($select->getSqlString($this->getPlatform('READ'))); die();
		$totalFriends = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select )->toArray ();
		if ($totalFriends) {
			return count ( $totalFriends );
		} else {
			return 0;
		}
	}
	public function getTotalUserFriends($userId) {
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$select->columns ( array (
				
				'total_friend' => new Expression ( 'COUNT(id)' ) 
		) );
		$select->where ( array (
				'user_id' => $userId,
				'status' => 1 
		) );
		
		// var_dump($select->getSqlString($this->getPlatform('READ')));
		
		$totalFriend = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select )->current ();
		return $totalFriend;
	}
	public function getFriendListForCurrentUser($userId){
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$select->columns ( array (
				'friend_id',
				'name' => new Expression ( 'CONCAT( first_name,  "  ", last_name ) ' ) 
		) );
		$select->join ( array (
				'rs' => 'users'
		), 'rs.id =  user_friends.friend_id', array (
				'email','display_pic_url_normal','display_pic_url'
		), $select::JOIN_INNER );
		$select->where ( array (
				'user_friends.user_id' => $userId,
				'user_friends.status' => 1
		) );
		
		$listFriend = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select )->toArray ();
        $commonFunctions = new CommonFunctions();
        if(count($listFriend)>0){
            foreach($listFriend as $key=>$val){
            $userData=array('id'=>$val['friend_id'],'display_pic_url_normal'=>$val['display_pic_url_normal'],'display_pic_url'=>$val['display_pic_url']);    
            $listFriendImage = $commonFunctions->checkProfileImageUrl($userData);
            $listFriend[$key]['display_pic_url']=$listFriendImage['display_pic_url'];
            }
        }
        return $listFriend;
	}
	public function updateFriends($data) {
		
		
		$writeGateway = $this->getDbTable ()->getWriteGateway ();
		$dataUpdated = array ();
		
			$dataUpdated = $writeGateway->update ( array('status'=>1), array (
					'user_id' => $data ['user_id'],
					'friend_id' => $data ['friend_id'],
					'status' => 2
			) );
		
		if($dataUpdated){
			return true;
		}
		else{
			return false;
		}
	}
    
    /**
     * Check if $user is friend of $other_user
     * @param int $user_id
     * @param int $other_user_id
     * @return int 0=not friend, 1=freind, 2=invitation pending
     */
    public function isFriend($user_id, $other_user_id) {
        $select = new Select ();
        $select->from($this->getDbTable()->getTableName());
        $where = new Where ();
        $where->equalTo('user_id', $user_id);
        $where->equalTo('friend_id', $other_user_id);
        $where->equalTo('status', 1);
        $select->where($where);
        //pr($select->getSqlString());
        $friends = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->toArray();
        return count($friends) > 0 ? 1 : 0;
    }
    
    public function getCheckinUserFriendList($userId=false){
        $select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$select->columns ( array (
				'friend_id' 
		) );
		$select->join ( array (
				'rs' => 'users'
		), 'rs.id =  user_friends.friend_id', array ('first_name','last_login'), $select::JOIN_INNER );
		$select->where ( array (
				'user_friends.user_id' => $userId,
				'user_friends.status' => 1
		) );
		
		$listFriend = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select )->toArray ();
        return $listFriend;
    }
    
    public function getUserInvitationId($id=false,$fId=false){
        $select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$select->columns ( array (
				'invitation_id' 
		) );
		$select->where ( array (
				'user_friends.user_id' => $id,
                'user_friends.friend_id' => $fId,
				'user_friends.status' => 1
		) );
		
		$listFriend = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select )->toArray ();
        return $listFriend;
    }

}