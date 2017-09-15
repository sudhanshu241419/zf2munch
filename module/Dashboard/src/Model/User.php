<?php

namespace Dashboard\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;



class User extends AbstractModel {

    public $id;
    public $user_name;
    public $first_name;
    public $last_name;
    public $email;
    public $password;
    public $mobile;
    public $phone;
    public $display_pic_url;
    public $user_source;
    public $accept_toc;
    public $newsletter_subscribtion;
    public $created_at;
    public $update_at;
    public $points;
    public $billing_address;
    public $shipping_address;
    public $status;
    public $display_pic_url_normal;
    public $display_pic_url_large;
    public $delivery_instructions;
    public $takeout_instructions;
    public $order_msg_status = 0;
    public $session_token;
    public $last_login;
    public $access_token;
    protected $_db_table_name = 'Dashboard\Model\DbTable\UserTable';
    protected $_primary_key = 'id';
    public $city_id;
    public $bp_status;
    public $registration_subscription = 0;
    public $wallpaper = NULL;
    public $referral_code = NULL;
    public $referral_ext = NULL;
    public $wallet_balance = 0;

    public function getUserDetail(array $options = array()) {
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $response = $this->find($options)->current();
        return $response;
    }
    public function updateUserPoint($id, $point) {
        $data = array(
            'points' => $point
        );
        $writeGateway = $this->getDbTable()->getWriteGateway();
        $dataUpdated = $writeGateway->update($data, array(
            'id' => $id
        ));
        if ($dataUpdated == 0) {
            return array(
                'error' => 'User point not found'
            );
        } else {
            return array(
                'success' => 'true'
            );
        }
    }
    public function countUserPoints($user_id) {
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(
            'points'
        ));
        $where = new Where();
        $where->equalTo('id', $user_id);
        $where->equalTo('status', 1);
        $select->where($where);
        $userPoints = $this->getDbTable()
                ->setArrayObjectPrototype('ArrayObject')
                ->getReadGateway()
                ->selectWith($select);
        return $userPoints->toArray();
    }
    
    public function dineAndMoreUserDetails($restaurantId){
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $tags = new \Restaurant\Model\Tags();
        $tagsDetails = $tags->getTagDetailByName("dine-more");
        
        $joins = array();
        
        $joins [] = array(
            'name' => array(
                'rs'=>'restaurant_servers'
            ),
            'on' => 'rs.user_id = users.id',
            'columns' => array(
                'restaurant_id',
                'code',
            ),
            'type' => 'inner'
        );       
        $joins[] = array(
            'name' => array(
                'rt'=>'restaurant_tags'
            ),
            'on' => 'rs.restaurant_id = rt.restaurant_id',
            'columns' => array('tag_id','rest_short_url'),
            'type' => 'inner'
        );   
        
        $options = array(
            'columns' => array(
                'first_name',
                'last_name',
                'email','id'
            ),
        'where' => array('rt.status'=>1,'rs.restaurant_id'=>$restaurantId), //array('users.city_id' => 18848),  
        'joins' => $joins,
        'group' => 'users.email'
            
        );
        
        $userDineAndMoreRestaurant = $this->find($options)->toArray();        
        return $userDineAndMoreRestaurant;
     }
     public function getUserPicture($userId = 0) {
        if ($userId != null && $userId != '') {
            $select = new Select();
            $select->from($this->getDbTable()
                            ->getTableName());
            $select->columns(array(
                'picture'=>'display_pic_url_large'
            ));
            $where = new Where();
            $where->equalTo('id', $userId);
            $select->where($where);
            $userPicture = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->current();
            if (!empty($userPicture)) {
                if(preg_match("@^http://@i",$userPicture['picture']) || preg_match("@^https://@i",$userPicture['picture'])){
                    return $userPicture['picture'];
                } else {
                    return ($userPicture['picture']!='') ? USER_REVIEW_IMAGE."profile/".$userId."/".$userPicture['picture']:'';
                }
            } 
        } else {
            return '';
        }
    }
    public function getUserDefaultPicture($userId = 0) {
        if ($userId != null && $userId != '') {
            $select = new Select();
            $select->from($this->getDbTable()
                            ->getTableName());
            $select->columns(array(
                'picture'=>'display_pic_url_large'
            ));
            $where = new Where();
            $where->equalTo('id', $userId);
            $select->where($where);
            $userPicture = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->current();
            if (!empty($userPicture)) {
               return $userPicture['picture'];
            } 
        } else {
            return '';
        }
    }
    public function updateUserPoints($userId, $reservationId, $firstReservationPoint, $reservationPoints) {
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(
            'id',
            'points'
        ));
        $where = new Where ();
        $where->equalTo('id', $userId);
        $select->where($where);
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $data = [];
        $invitedPoints = 0;
        $userInvitaion = new UserReservationInvitation();
        $totalInvitee = $userInvitaion->getUserInvitations($reservationId);
        $invitedPoints = $totalInvitee * $reservationPoints;
        $responce = $this->getDbTable()
                        ->setArrayObjectPrototype('ArrayObject')
                        ->getReadGateway()
                        ->selectWith($select)->current();
        if (!empty($responce)) {
            $userTotalPoints = (int) $responce['points'];
            $totalPoints = $userTotalPoints + $reservationPoints + $invitedPoints;
            $record = $this->updateUserPoint($responce['id'], $totalPoints);
            if ($record) {
                return true;
            }
            return false;
        } else {
            return false;
        }
    }
    public function getUserName($userId) {
        $userName = "";
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(
            'first_name',
            'email'
        ));
        $where = new Where ();
        $where->equalTo('id', $userId);
        $select->where($where);
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $user = $this->getDbTable()
                        ->setArrayObjectPrototype('ArrayObject')
                        ->getReadGateway()
                        ->selectWith($select)->current();
        if (!empty($user)) {
                if (!empty($user['first_name'])) {
                    $userName = $user['first_name'];
                } else {
                    $useremail = explode("@", $user['email']);
                    $userName = $useremail[0];
                }
            return $userName;
        }
    }
    public function getGuestDetail($id, $restId) {
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(
            'id',
            'username' => new Expression("CONCAT(first_name,' ',last_name)"),
            'email',
            'phone',
            'display_pic_url',
            'created_at',
        ));
        $select->join(array(
            'ua' => 'user_addresses'
                ), 'users.id = ua.user_id', array(
            'address' => new Expression("CONCAT(street, ', ',IF(apt_suite<>'',concat(apt_suite, ', '),''), city, ', ', zipcode)"),
                ), $select::JOIN_LEFT);
        $where = new Where ();
        $where->equalTo('users.id', $id);
        $select->where($where);
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $guest = $this->getDbTable()
                        ->setArrayObjectPrototype('ArrayObject')
                        ->getReadGateway()
                        ->selectWith($select)->current();
        $userPoints = new UserPoint();
        $guestPoint = $userPoints->getGuestPoints($id, $restId);
        $guest['total_points'] = (isset($guestPoint['total_points']) && $guestPoint['total_points']>0)?$guestPoint['total_points']:0;
        $guest['redeem_points'] = (isset($guestPoint['redeemed_points']) && $guestPoint['redeemed_points']>0)?$guestPoint['redeemed_points']:0;
        $guest['display_pic_url'] = $this->getUserPicture($id);
        return $guest;
    }
    public function getGuestOffers($id,$restId) {
        $userOffers = [];
        $dealCouponsModel = new DealsCoupons();
        $offers = $dealCouponsModel->getUsersOffers($id, $restId);
        $availed = $dealCouponsModel->getUsersOffersAvailed($id, $restId);
        $userOffers['offer'] = (isset($offers['total_offers'])) ? $offers['total_offers'] : 0;
        $userOffers['availed'] = (isset($offers['total_availed'])) ? $offers['total_availed'] : 0;
        return $userOffers;
    }
    public function getUserDetails($userId) {
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(
            '*'
        ));
        $where = new Where ();
        $where->equalTo('id', $userId);
        $select->where($where);
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $user = $this->getDbTable()
                        ->setArrayObjectPrototype('ArrayObject')
                        ->getReadGateway()
                        ->selectWith($select)->current();
        if (!empty($user)) {
            return $user;
        }else{
            return [];
        }
    }
}
