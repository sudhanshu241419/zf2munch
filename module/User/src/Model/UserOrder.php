<?php

namespace User\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;
use MCommons\StaticOptions;
use User\UserFunctions;

class UserOrder extends AbstractModel {

    public $id;
    public $user_id;
    public $restaurant_id;
    public $fname;
    public $lname;
    public $state_code;
    public $phone;
    public $apt_suite;
    public $city;
    public $order_amount;
    public $deal_discount;
    public $order_type;
    public $created_at;
    public $updated_at;
    public $user_comments;
    public $restaurants_comments;
    public $special_checks;
    public $zipcode;
    public $payment_status;
    public $status;
    public $delivery_time;
    public $tax;
    public $tip_amount;
    public $tip_percent;
    public $delivery_charge;
    public $delivery_address;
    public $frozen_status;
    public $user_sess_id;
    public $stripes_token;
    public $card_number;
    public $name_on_card;
    public $card_type;
    public $expired_on;
    public $billing_zip;
    public $payment_receipt;
    public $order_type1;
    public $order_type2;
    public $email;
    public $miles_away;
    public $stripe_card_id;
    public $user_card_id;
    public $total_amount;
    public $new_order = 1;
    public $approved_by = 0;
    public $is_read = 0;
    public $crm_update_at = '';
    public $host_name;
    public $is_deleted = 0;
    public $crm_comments = '';
    public $is_reviewed = 0;
    public $review_id;
    public $stripe_charge_id;
    protected $_db_table_name = 'User\Model\DbTable\UserOrderTable';
    protected $_primary_key = 'id';
    public $promocode_discount;
    public $deal_id;
    public $deal_title;
    public $order_pass_through = 0;
    public $encrypt_card_number = NULL;
    public $user_ip = NULL;
    public $address = NULL;
    public $longitude = 0;
    public $latitude = 0;
    public $city_id = 0;
    public $pay_via_point;
    public $pay_via_card;
    public $redeem_point;
    
    public $cod=0;

    public function getTotalOrderOfUser(array $options = array()) {
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(
            'total_order' => new Expression('COUNT(id)')
        ));
        $select->where(array(
            'user_id' => $options['columns']['user_id']
        ));
        $select->where('status', array(
            'placed',
            'ordered',
            'confirmed',
            'delivered',
            'arrived'
        ));
        $select->group('user_id');

        // var_dump($select->getSqlString($this->getPlatform('READ')));

        $totalOrder = $this->getDbTable()
                ->setArrayObjectPrototype('ArrayObject')
                ->getReadGateway()
                ->selectWith($select);
        return $totalOrder;
    }

    public function addtoUserOrder() {
        $data = $this->toArray();
        $writeGateway = $this->getDbTable()->getWriteGateway();
        if (!$this->id) {
            $rowsAffected = $writeGateway->insert($data);
        } else {
            $rowsAffected = $writeGateway->update($data, array(
                'id' => $this->id
            ));
                }
        // Get the last insert id and update the model accordingly
        $lastInsertId = $writeGateway->getAdapter()
                ->getDriver()
                ->getLastGeneratedValue();

        if ($rowsAffected >= 1) {
            $this->id = $lastInsertId;
            return $this->toArray();
        }
        return false;
    }

    public function getUserOrder(array $options = array()) {
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        return $this->find($options)->current();
    }

    public function userlastorder($userId, $date, $orderType, $status) {

        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(
            'id',
            'restaurant_id',
            'created_at',
            'total_amount',
            'delivery_time',
            'status',
            'order_type',
            'restaurants_comments',
            'crm_comments',
            'is_reviewed'
        ));
        $select->join(array(
            'rs' => 'restaurants'
                ), 'rs.id =  user_orders.restaurant_id', array(
            'restaurant_name'
                ), $select::JOIN_INNER);
        /*
         * $select->join(array( 'co' => 'cron_order' ), 'co.order_id = user_orders.id', array( 'archive_time' ), $select::JOIN_LEFT);
         */
        $where = new Where();
        $where->equalTo('user_orders.user_id', $userId);
        $where->notEqualTo('order_type', 'Dinein');
        $where->equalTo('user_orders.order_type1', $orderType);
        // $where->in('user_orders.status', $status);
        if ($status[0] == 'rejected') {
            $where->equalTo('user_orders.status', 'rejected')->AND->greaterThan('user_orders.delivery_time', $date);
        } else {
            //$where->NEST->in('user_orders.status', $status)->UNNSET->OR->NEST->equalTo('user_orders.status', 'rejected')->AND->greaterThan('user_orders.delivery_time', $date)->UNNEST->UNNEST;
            $where->in('user_orders.status', $status);
        }
        /*
         * if ($orderType == 'G') { $where->greaterThanOrEqualTo('user_orders.delivery_time', $date); } else { $where->greaterThan('user_orders.delivery_time', $date); }
         */
        $select->where($where);
        if ($orderType == 'G') {
            $select->order('user_orders.delivery_time desc');
        } else {
            $select->order('user_orders.delivery_time ASC');
        }
        // $select->limit(1);
// var_dump($select->getSqlString($this->getPlatform('READ'))); die();
        $orderData = $this->getDbTable()
                ->setArrayObjectPrototype('ArrayObject')
                ->getReadGateway()
                ->selectWith($select)
                ->toArray();
        return $orderData;
    }

    public function getTotalOrder($userId, $status) {
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(
            'total_order' => new Expression('COUNT(id)')
        ));
        $where = new Where();
        $where->equalTo('user_id', $userId);
        // $where->notEqualTo('status', $status);
        $select->where($where);
        $totalOrder = $this->getDbTable()
                ->setArrayObjectPrototype('ArrayObject')
                ->getReadGateway()
                ->selectWith($select)
                ->current();
        return $totalOrder;
    }

    public function dateToString($orderTime, $date) {
        $difference = strtotime($date) - strtotime($orderTime);
        $periods = array(
            "second",
            "minute",
            "hour",
            "day",
            "week",
            "month",
            "years",
            "decade"
        );
        $lengths = array(
            "60",
            "60",
            "24",
            "7",
            "4.35",
            "12",
            "10"
        );
        for ($j = 0; $difference >= $lengths[$j]; $j ++)
            $difference /= $lengths[$j];
        $difference = round($difference);
        if ($difference != 1)
            $periods[$j] .= "s";

        $text = "Today, $difference $periods[$j] ago";
        return $text;
    }

    public function getCurrentNotificationOrder($user_id, $today) {
        $output = array();
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(
            'id',
            'created_at'
        ));
        $select->where(array(
            'user_id' => $user_id
        ));
        $select->where->greaterThan('delivery_time', $today);
        $select->order('delivery_time DESC');
        $currentNotification = $this->getDbTable()
                ->setArrayObjectPrototype('ArrayObject')
                ->getReadGateway()
                ->selectWith($select)
                ->toArray();

        if (!empty($currentNotification)) {

            $output['order_created_time'] = 'available';
        }

        return $output;
    }

    public function getAllOrder(array $options = array()) {
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $orders = $this->find($options)->toArray();
        return $orders;
    }

    /**
     * Get User Live Order
     *
     * @param unknown $options            
     * @return \ArrayObject
     */
    public function getUserLiveOrder($options = array()) {
        $res = array();
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(
            'id',
            'delivery_time',
            'total_amount',
            'status',
            'created_at',
            'restaurants_comments',
            'order_type',
            'crm_comments',
            'is_reviewed',
            'review_id',
            'restaurant_id'
        ));
        $select->join(array(
            'rs' => 'restaurants'
                ), 'rs.id = user_orders.restaurant_id', array(
            'restaurant_name',
            'restaurant_id' => 'id'
                ), $select::JOIN_INNER);
        $select->join(array(
            'uod' => 'user_order_details'
                ), 'uod.user_order_id = user_orders.id', array(
            'order_item_id' => 'id',
            'item_name' => 'item',
            'item_qty' => 'quantity',
             'item_id'
                    
                ), $select::JOIN_INNER);
        /*
         * $select->join(array( 'co' => 'cron_order' ), 'co.order_id = user_orders.id', array( 'archive_time' ), $select::JOIN_LEFT);
         */
        $where = new Where();
        if(isset($options['restaurantId']) && $options['restaurantId']){
            $where->equalTo('user_orders.restaurant_id', $options['restaurantId']);
        }
        $where->equalTo('user_orders.user_id', $options['userId']);
        $where->notEqualTo('user_orders.order_type', 'Dinein');
        $where->NEST->in('user_orders.status', $options['orderStatus'])->UNNSET->OR->NEST->equalTo('user_orders.status', 'rejected')->AND->greaterThan('user_orders.delivery_time', $options['currentDate'])->UNNEST->UNNEST;
        // $where->greaterThanOrEqualTo('user_orders.delivery_time', $options['currentDate']);
        $select->where($where);
        $select->order('user_orders.created_at ASC');
        // var_dump($select->getSqlString($this->getPlatform('READ'))); die();
        $resDetail = $this->getDbTable()
                ->setArrayObjectPrototype('ArrayObject')
                ->getReadGateway()
                ->selectWith($select)
                ->toArray();
        if ($resDetail) {
            $currentTime = $options['currentDate'];

            $orderId = array_unique(array_map(function ($i) {
                        return $i['id'];
                    }, $resDetail));

            $i = 0;
            $response = array();
            foreach ($resDetail as $key => $value) {

                $response[] = $value;
            }

            $order = $this->refineLiveOrder($response, $orderId, $currentTime);
            $userFunction = new UserFunctions();
            $res = $userFunction->ReplaceNullInArray($order);
        }
        return $res;
    }

    /**
     * Get User Archive Order
     *
     * @param unknown $options            
     * @return Array
     */
    public function getUserArchiveOrder($options = array()) {
        $res = array();

        if (is_numeric($options['limit'])) {

            $limit = $options['limit'];
        }

        $orderBy = 'user_orders.created_at DESC';
        if ($options['orderby'] == 'date') {
            $orderBy = 'user_orders.created_at DESC';
        } elseif ($options['orderby'] == 'amount') {
            $orderBy = 'user_orders.total_amount ASC';
        }
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(
            'id',
            'delivery_time',
            'total_amount',
            'status',
            'created_at',
            'order_type',
            'is_reviewed',
            'review_id',
            'restaurant_id'
        ));
        $select->join(array(
            'rs' => 'restaurants'
                ), 'rs.id = user_orders.restaurant_id', array(
            'restaurant_name',
            'restaurant_id' => 'id',
            'closed',
            'inactive',
            'city_id',
            'accept_cc_phone',
                ), $select::JOIN_INNER);
        $select->join(array(
            'city' => 'cities'
                ), 'city.id = rs.city_id', array(
            'city_name'
                ), $select::JOIN_INNER);
        $select->join(array(
            'uod' => 'user_order_details'
                ), 'uod.user_order_id = user_orders.id', array(
            'order_item_id' => 'id',
            'item_name' => 'item',
            'item_qty' => 'quantity',
            'item_id'
                ), $select::JOIN_LEFT);

        $select->join(array(
            'm' => 'menus'
                ), 'uod.item_id = m.id', array(
            'menu_status' => 'status',
            'online_order_allowed'
                ), $select::JOIN_LEFT);
        /*
         * $select->join(array( 'co' => 'cron_order' ), 'co.order_id = user_orders.id', array( 'archive_time' ), $select::JOIN_LEFT);
         */
        $where = new Where();
        
        if($options['restaurantId']){
            $where->equalTo('user_orders.restaurant_id', $options['restaurantId']);
        }
        $where->equalTo('user_orders.user_id', $options['userId']);
        $where->notEqualTo('order_type', 'Dinein');
        if (!empty($options['group'])) {
            $where->in('user_orders.user_id', $options['userId']);
            $select->group('user_id');
        }
        $where->NEST->equalTo('user_orders.status', 'archived')->
                        OR->NEST->equalTo('user_orders.status', 'rejected')->AND->
                        lessThan('user_orders.delivery_time', $options['currentDate'])->UNNEST->
                OR->NEST->equalTo('user_orders.status', 'cancelled')->UNNEST->UNNEST;
        // $where->notEqualTo('user_orders.status', $options['orderStatus'][4]);
        $select->where($where);
        $select->order($orderBy);

        ///var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $resDetail = $this->getDbTable()
                ->setArrayObjectPrototype('ArrayObject')
                ->getReadGateway()
                ->selectWith($select)
                ->toArray();

        if ($resDetail) {
            $orderId = array_unique(array_map(function ($i) {
                        return $i['id'];
                    }, $resDetail));

            $i = 0;
            $response = array();
            foreach ($resDetail as $key => $value) {
                $response[] = $value;
            }

            $archieveOrder = $this->refineOrder($response, $orderId, $options['currentDate'], $options['orderby']);
            //pr($archieveOrder,1);
            $joinAddons = $this->getAddonJoin();
            $userOrderAddonsModel = new UserOrderAddons();
           // pr($archieveOrder,1);
            foreach($archieveOrder as $key =>$val){
                $menu = new \Restaurant\Model\Menu();
                $menuoptions = array("columns"=>array('status','online_order_allowed'),"where"=>array('id'=>$val['menu_id']));
                $mStatus = $menu->getMenuStatus($menuoptions);
                $archieveOrder[$key]['menu_status'] = 0;
                if(!empty($mStatus)){
                foreach($mStatus as $k => $v){
                    if($v['status'] ==1 && $v['online_order_allowed']==1){
                        $archieveOrder[$key]['menu_status'] = 1;
                    }
                }
                }else{
                    $archieveOrder[$key]['menu_status'] = 0;
                }
               // $this->changeMenuStatusOnAddons($archieveOrder,$val['menu_id'],$val['order_detail_id'],$joinAddons,$userOrderAddonsModel,$key);
            }
            if (empty($options['flag'])) {
                $archieveOrder = array_slice($archieveOrder, $options['offset'], $options['limit']);
            }
            $user_function = new UserFunctions();
            $res = $user_function->ReplaceNullInArray($archieveOrder);
        }
        return $res;
    }
    
    public function getAddonJoin(){
        $joins_addons = [];
        $joins_addons [] = array(
                'name' => array(
                    'ma' => 'menu_addons'
                ),
                'on' => new \Zend\Db\Sql\Expression("user_order_addons.menu_addons_option_id=ma.id"),
                'columns' => array(
                    'addon_status' => 'status',
                    'selection_type'
                ),
                'type' => 'left'
            );
        return $joins_addons;
    }
    
    public function changeMenuStatusOnAddons(&$archieveOrder,$menuId,$orderDetailId,$joinAddons,$userOrderAddonsModel,$dkey){
        if(!empty($menuId)){     
          pr($archieveOrder);
//            pr($dkey);
            foreach($menuId as $mkey => $singlemenuId){ 
                //pr($archieveOrder[$dkey]['order_detail_id'][$singlemenuId]);
                $addon = $userOrderAddonsModel->getAllOrderAddon(array(
                        'columns' => array(
                            'menu_addons_id',
                            'menu_addons_option_id',
                            'addons_option',
                            'price',
                            'quantity',
                            'priority',
                            'was_free'
                        ),
                        'where' => array(
                            'user_order_detail_id' => $orderDetailId[$singlemenuId]
                        ),
                        'joins'=>$joinAddons
                    ));
                //pr($addon);
                foreach ($addon as $akey => $result) {
                   if($result['selection_type']==0 && $result['addon_status']==0){
                      $archieveOrder[$dkey]['menu_status'] = 0;
                   }else{
                      $archieveOrder[$dkey]['menu_status'] = 1;
                      //pr($archieveOrder,1);
                      break;
                   }
                }
            }
        }
        
    }

    public function getUserLiveOrderForMob($options = array()) {
        $res = array();
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(
            'id',
            'delivery_time',
            'total_amount',
            'status',
            'created_at',
            'order_type1',
            'order_type',
            'restaurant_id',
            'is_reviewed',
            'review_id'
        ));
        $select->join(array(
            'rs' => 'restaurants'
                ), 'rs.id = user_orders.restaurant_id', array(
            'restaurant_name',
            'restaurant_id' => 'id',
            'is_restaurant_exists' => new Expression('if(inactive = 1 or closed = 1,"No","Yes")'),
            'accept_cc_phone',
            'menu_without_price',
            'delivery',
            'takeout'                  
        ), $select::JOIN_INNER);
        $select->join(array(
            'uod' => 'user_order_details'
                ), 'uod.user_order_id = user_orders.id', array(
            'order_item_id' => 'id',
            'item_name' => 'item',
            'item_id',
            'item_qty' => 'quantity'
                ), $select::JOIN_INNER);

        $select->join(array(
            'm' => 'menus'
                ), 'uod.item_id = m.id', array(
            'menu_status' => 'status'
                ), $select::JOIN_LEFT);

        $where = new Where();

        if (isset($options['userId']) && !empty($options['userId'])) {
            $where->equalTo('user_orders.user_id', $options['userId']);
        } else {
            $where->equalTo('user_orders.email', $options['email']);
        }
        $where->notEqualTo('user_orders.order_type', 'Dinein');
        $where->NEST->in('user_orders.status', $options['orderStatus'])->UNNSET->OR->NEST->equalTo('user_orders.status', 'rejected')->AND->greaterThan('user_orders.delivery_time', $options['currentDate'])->UNNEST->UNNEST;


        //$where->in('user_orders.status', $options['orderStatus']);
        //$where->notEqualTo('user_orders.order_type', 'Dinein');
        //$where->greaterThanOrEqualTo('user_orders.delivery_time', $options['currentDate']);
        $select->where($where);
        $select->order('user_orders.created_at DESC');

        //pr($select->getSqlString($this->getPlatform('READ')),true);
        $resDetail = $this->getDbTable()
                ->setArrayObjectPrototype('ArrayObject')
                ->getReadGateway()
                ->selectWith($select)
                ->toArray();

        if ($resDetail) {
            $currentTime = $options['currentDate'];

            $orderId = array_unique(array_map(function ($i) {
                        return $i['id'];
                    }, $resDetail));

            $i = 0;
            $response = array();
            foreach ($resDetail as $key => $value) {

                $response[] = $value;
            }

            $order = $this->refineOrderForMob($response, $orderId, $currentTime);
            $userFunction = new UserFunctions();
            $res = $userFunction->ReplaceNullInArray($order);
        }
        return $res;
    }

    private function refineOrderForMob($userOrders, $orderId, $currentTime = NULL) {
        $response = array();
        $orders = array();
        $items = array();
        $orderData = array();
        $index = 0;
        $orderindex = 0;
        $i = 0;

        foreach ($orderId as $k => $v) {
            $description = '';
            foreach ($userOrders as $key => $value) {
                if ($v == $value['id']) {
                    $i ++;
                    $orders[$value['id']]['menu_id'][] = $value['item_id'];
                    $orders[$value['id']]['id'] = $value['id'];
                    $orders[$value['id']]['restaurant_id'] = $value['restaurant_id'];
                    $orders[$value['id']]['restaurant_name'] = $value['restaurant_name'];
                    $orders[$value['id']]['is_restaurant_exists'] = $value['is_restaurant_exists'];
                    $orders[$value['id']]['order_type1'] = $value['order_type1'];
                    $orders[$value['id']]['order_type'] = $value['order_type'];
                    $orders[$value['id']]['delivery_date'] = $value['delivery_time'];
                    $orders[$value['id']]['order_date'] = $value['created_at'];
                    $orders[$value['id']]['menu_status'] = 0;
                    $orders[$value['id']]['is_reviewed'] = ($value['is_reviewed']==1)?1:0;
                    $orders[$value['id']]['review_id'] = ($value['review_id'])?$value['review_id']:0;
                    $orders[$value['id']]['order_allow'] = (int)0;
                    if($value['order_type']==='Delivery'){
                        if($value['delivery'] == 1 && $value['menu_without_price'] == 0 && $value ['accept_cc_phone'] == 1){
                            $orders[$value['id']]['order_allow'] = (int)1;
                        }
                    }elseif($value['order_type']==='Takeout'){
                        if($value['takeout'] == 1 && $value['menu_without_price'] == 0 && $value['accept_cc_phone'] == 1){
                            $orders[$value['id']]['order_allow'] = (int)1;
                        }
                    }
                   
//                    $createdAt = StaticOptions::getFormattedDateTime($value['created_at'], 'Y-m-d H:i:s', 'Y-m-d');
//                    $orderCreateAt = StaticOptions::getFormattedDateTime($value['created_at'], 'Y-m-d H:i:s', 'Y-m-d H:i:s');
//                    if (! empty($currentTime)) {
//                        $conpDate = StaticOptions::getFormattedDateTime($currentTime, 'Y-m-d H:i:s', 'Y-m-d');
//                        
//                        if ($createdAt == $conpDate) {
//                            $orders[$value['id']]['order_date'] = $this->dateToString($orderCreateAt, $currentTime);
//                        } else {
//                            $orders[$value['id']]['order_date'] = StaticOptions::getFormattedDateTime($value['created_at'], 'Y-m-d H:i:s', 'M d, Y');
//                        }
//                    }
                    // $orders[$value['id']]['created_at'] = StaticOptions::getFormattedDateTime($value['created_at'], 'Y-m-d H:i:s', 'M d, Y');

                    $orders[$value['id']]['status'] = $value['status'];
                    $orders[$value['id']]['total_amount'] = number_format($value['total_amount'], 2, '.', '');

                    $orders[$value['id']]['item_list'][] = array('order_item_id' => $value['order_item_id'],
                        'item_name' => html_entity_decode(htmlspecialchars_decode($value['item_name'], ENT_QUOTES)),
                        'item_qty' => $value['item_qty']);
                }
            }
        }

        $key_index = 0;
        foreach ($orders as $key => $orderItem) {
            $orderResponse[$key_index] = $orderItem;
            $key_index ++;
        }
        return $orderResponse;
    }

    public function getUserArchiveOrderForMob($options = array()) {
        $res = array();
        $totalArchiveRecords = 0;
        if (is_numeric($options['limit'])) {
            $limit = $options['limit'];
        }

        $orderBy = 'user_orders.created_at DESC';
        if (isset($options['orderby']) && $options['orderby'] == 'date') {
            $orderBy = 'user_orders.created_at DESC';
        } elseif (isset($options['orderby']) && $options['orderby'] == 'amount') {
            $orderBy = 'user_orders.order_amount ASC';
        }
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(
            'id',
            'delivery_time',
            'total_amount',
            'status',
            'created_at',
            'order_type1',
            'order_type',
            'restaurant_id',
            'is_reviewed',
            'review_id'
        ));
        $select->join(array(
            'rs' => 'restaurants'
                ), 'rs.id = user_orders.restaurant_id', array(
            'restaurant_name',
            'restaurant_id' => 'id',
            'is_restaurant_exists' => new Expression('if(inactive = 1 or closed = 1,"No","Yes")'),
            'accept_cc_phone',
            'menu_without_price',
            'delivery',
            'takeout'                     
            ), $select::JOIN_INNER);
        $select->join(array(
            'uod' => 'user_order_details'
                ), 'uod.user_order_id = user_orders.id', array(
            'order_item_id' => 'id',
            'item_id',
            'item_name' => 'item',
            'item_qty' => 'quantity'
                ), $select::JOIN_INNER);

        $select->join(array(
            'm' => 'menus'
                ), 'uod.item_id = m.id', array(
            'menu_status' => 'status'
                ), $select::JOIN_LEFT);

        $where = new Where();

        if (isset($options['userId']) && !empty($options['userId'])) {
            $where->equalTo('user_orders.user_id', $options['userId']);
        } else {
            $where->equalTo('user_orders.email', $options['email']);
        }

        $where->notEqualTo('order_type', 'Dinein');
        $where->NEST->equalTo('user_orders.status', 'archived')->
                        OR->NEST->equalTo('user_orders.status', 'rejected')->AND->
                        lessThan('user_orders.delivery_time', $options['currentDate'])->UNNEST->
                OR->NEST->equalTo('user_orders.status', 'cancelled')->UNNEST->UNNEST;
        
        $select->where($where);
        $select->order($orderBy);

        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $resDetail = $this->getDbTable()
                ->setArrayObjectPrototype('ArrayObject')
                ->getReadGateway()
                ->selectWith($select)
                ->toArray();

        if ($resDetail) {
            $orderId = array_unique(array_map(function ($i) {
                        return $i['id'];
                    }, $resDetail));

            $i = 0;
            $response = array();
            foreach ($resDetail as $key => $value) {

                $response[] = $value;
            }

            $archieveOrder = $this->refineOrderForMob($response, $orderId);
            
            foreach($archieveOrder as $key =>$val){
                $menu = new \Restaurant\Model\Menu();
                $menuoptions = array("columns"=>array('status','online_order_allowed'),"where"=>array('id'=>$val['menu_id']));
                $mStatus = $menu->getMenuStatus($menuoptions);
                $archieveOrder[$key]['menu_status'] = 0;
                if(!empty($mStatus)){
                foreach($mStatus as $k => $v){
                    if($v['status'] ==1 && $v['online_order_allowed']==1){
                        $archieveOrder[$key]['menu_status'] = 1;
                    }
                }
                }else{
                    $archieveOrder[$key]['menu_status'] = 0;
                }
            }
            $totalArchiveRecords = count($archieveOrder);
            $archieveOrder = array_slice($archieveOrder, $options['offset'], $options['limit']);
            $user_function = new UserFunctions();
            $res = $user_function->ReplaceNullInArray($archieveOrder);
        }
        $res['archive_count'] = $totalArchiveRecords;
        return $res;
    }

    public function getCurrentOrderCount($userId, $status, $date) {
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(
            'total_order' => new Expression('COUNT(id)')
        ));
        $where = new Where();
        $where->equalTo('user_id', $userId);
        $where->notEqualTo('status', $status);
        $where->greaterThan('delivery_time', $date);
        $select->where($where);
        $totalOrder = $this->getDbTable()
                ->setArrayObjectPrototype('ArrayObject')
                ->getReadGateway()
                ->selectWith($select)
                ->current();
        return $totalOrder;
    }

    /**
     * Refine Order For User Archive Order
     *
     * @param unknown $userOrders            
     * @param unknown $orderId            
     * @return Array
     */
    private function refineOrder($userOrders, $orderId, $currentDate, $flag = NULL) {
        $response = array();
        $orders = array();
        $items = array();
        $orderData = array();
        $index = 0;
        $orderindex = 0;
        $live = false;
        $type = "";
        $orderResponse = array();
        $menu_status = 0;
        foreach ($orderId as $k => $v) {
            $description = '';
            foreach ($userOrders as $key => $value) {
                $live = false;
                // $archiveTime = $value['archive_time'];
                // $deliveryTime = $value['delivery_time'];
                if ($v == $value['id']) {
                    /*
                     * if (! empty($value['archive_time']) || $value['archive_time'] != NULL) { if (strtotime($archiveTime) < strtotime($currentDate)) { $live = true; } } elseif (strtotime($deliveryTime) < strtotime($currentDate)) { $live = true; } elseif ($value['status'] == 'cancelled') { $live = true; }
                     */

                    // if ($live) {
                    $orders[$value['id']]['order_detail_id'][$value['item_id']] = $value['order_item_id'];
                    $orders[$value['id']]['menu_id'][] = $value['item_id'];
                    $orders[$value['id']]['id'] = $value['id'];
                    $orders[$value['id']]['restaurant_id'] = $value['restaurant_id'];
                    $orders[$value['id']]['restaurant_name'] = $value['restaurant_name'];
                    $orders[$value['id']]['accept_cc_phone'] = $value['accept_cc_phone'];
                    $orders[$value['id']]['delivery_time'] = $value['delivery_time'];
                    $createdAt = StaticOptions::getFormattedDateTime($value['created_at'], 'Y-m-d H:i:s', 'Y-m-d');
                    $orderCreateAt = StaticOptions::getFormattedDateTime($value['created_at'], 'Y-m-d H:i:s', 'Y-m-d H:i:s');
                    $orders[$value['id']]['created_at'] = StaticOptions::getFormattedDateTime($value['created_at'], 'Y-m-d H:i:s', 'M d, Y');
                    $orders[$value['id']]['requested_delivery_time'] = StaticOptions::getFormattedDateTime($value['delivery_time'], 'Y-m-d H:i:s', 'H:i A');
                    $orders[$value['id']]['status'] = $value['status'];
                    $orders[$value['id']]['order_type'] = $value['order_type'];
                    if ($value['order_type'] == 'Takeout') {
                        $type = '2';
                    } elseif ($value['order_type'] == 'Delivery') {
                        $type = '1';
                    }
                    $orders[$value['id']]['type'] = $type;
                    $orders[$value['id']]['total_amount'] = number_format($value['total_amount'], 2);
                    $description .= $value['item_qty'] . ' ' . $value['item_name'] . ',';
                    $orders[$value['id']]['menu_status'] = $menu_status;
                    $orders[$value['id']]['description'] = substr($description, 0, - 1);
                    $orders[$value['id']]['order_status'] = 'archive';
                    $orders[$value['id']]['is_reviewed'] = $value['is_reviewed'];
                    $orders[$value['id']]['review_id'] = $value['review_id'];
                    $orders[$value['id']]['is_live'] = 'no';
                    $orders[$value['id']]['closed'] = ($value['closed'] == 1) ? 'yes' : 'no';
                    $orders[$value['id']]['inactive'] = ($value['inactive'] == 1) ? 'yes' : 'no';
                    $orders[$value['id']]['city_id'] = $value['city_id'];
                    $orders[$value['id']]['city_name'] = $value['city_name'];
                    // }
                }
            }
        }

        $key_index = 0;
        if (!empty($orders)) {
            foreach ($orders as $key => $orderItem) {
                $orderResponse[$key_index] = $orderItem;
                $key_index ++;
            }
            if ($flag === 'restaurant' && !empty($orderResponse)) {
                foreach ($orderResponse as $key => $order) {
                    $name[] = $order['restaurant_name'];
                }
                array_multisort($name, SORT_ASC, $orderResponse);
            }
        }

        return $orderResponse;
    }

    /**
     * Refine Order For Live Order
     *
     * @param unknown $userOrders            
     * @param unknown $orderId            
     * @param string $currentTime            
     * @return Array
     */
    private function refineLiveOrder($userOrders, $orderId, $currentTime) {
        $orderResponse = array();
        $response = array();
        $orders = array();
        $items = array();
        $orderData = array();
        $index = 0;
        $orderindex = 0;
        $live = false;
        $userFunction = new UserFunctions();
        $orders = '';
        foreach ($orderId as $k => $v) {
            $description = '';

            foreach ($userOrders as $key => $value) {
                $live = false;
                /*
                 * $archiveTime = $value['archive_time']; $deliveryTime = $value['delivery_time']; if (! empty($value['archive_time']) || $value['archive_time'] != NULL) { if (strtotime($archiveTime) >= strtotime($currentTime)) { $live = true; } } elseif (strtotime($deliveryTime) >= strtotime($currentTime)) { $live = true; }
                 */

                // if ($live == true) {
                if ($v == $value['id']) {
                    $orders[$value['id']]['id'] = $value['id'];
                    $orders[$value['id']]['restaurant_id'] = $value['restaurant_id'];
                    $orders[$value['id']]['restaurant_name'] = $value['restaurant_name'];
                    $orders[$value['id']]['delivery_time'] = StaticOptions::getFormattedDateTime($value['delivery_time'], 'Y-m-d H:i:s', 'M d, Y');
                    $orders[$value['id']]['restaurants_comments'] = isset($value['restaurants_comments']) ? $value['restaurants_comments'] : $value['crm_comments'];
                    $createdAt = StaticOptions::getFormattedDateTime($value['created_at'], 'Y-m-d H:i:s', 'Y-m-d');
                    $orderCreateAt = StaticOptions::getFormattedDateTime($value['created_at'], 'Y-m-d H:i:s', 'Y-m-d H:i:s');
                    if (!empty($currentTime)) {
                        $conpDate = StaticOptions::getFormattedDateTime($currentTime, 'Y-m-d H:i:s', 'Y-m-d');

                        if ($createdAt == $conpDate && $value['status'] != 'placed') {
                            $orders[$value['id']]['order_date'] = $this->dateToString($orderCreateAt, $currentTime);
                        } else {
                            $orders[$value['id']]['order_date'] = StaticOptions::getFormattedDateTime($value['created_at'], 'Y-m-d H:i:s', 'M d, Y');
                        }
                    }
                    $orders[$value['id']]['created_at'] = StaticOptions::getFormattedDateTime($value['created_at'], 'Y-m-d H:i:s', 'M d, Y');
                    $orders[$value['id']]['requested_delivery_time'] = StaticOptions::getFormattedDateTime($value['delivery_time'], 'Y-m-d H:i:s', 'h:i A');
                    $orders[$value['id']]['status'] = $value['status'];
                    $orders[$value['id']]['is_reviewed'] = $value['is_reviewed'];
                    $orders[$value['id']]['review_id'] = $value['review_id'];
                    $orders[$value['id']]['total_amount'] = number_format($value['total_amount'], 2);
                    $orders[$value['id']]['order_type'] = ucfirst($value['order_type']);
                    $description[] = $value['item_qty'] . ' ' . $value['item_name'];

                    $orders[$value['id']]['description'] = @implode(",", $description);
                    $orders[$value['id']]['short_description'] = @implode(",", $description);

                    if ($value['status'] == 'rejected') {
                        $orders[$value['id']]['order_state'] = 'rejected';
                    } elseif ($value['status'] == 'ordered' || $value['status'] == 'arrived' || $value['status'] == 'delivered' || $value['status'] == 'confirmed' || $value['status'] == 'ready') {
                        $orders[$value['id']]['order_state'] = 'live';
                    } elseif ($value['status'] == 'placed') {
                        $orders[$value['id']]['order_state'] = 'pending';
                    }
                }
                // }
            }
        }

        $key_index = 0;
        if (!empty($orders)) {
            array_multisort($orders, SORT_DESC, $orders);
        }
        if (!empty($orders)) {
            foreach ($orders as $key => $orderItem) {
                $orderResponse[$key_index] = $orderItem;
                $key_index ++;
            }
        }

        return $orderResponse;
    }

    /**
     * Get Usre Order Details
     *
     * @param unknown $user_id            
     * @return <\ArrayObject >
     */
    public function getUserOrderDetails($user_id) {
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(
            'id',
            'created_at'
        ));
        $select->join(array(
            'rs' => 'restaurants'
                ), 'rs.id = user_orders.restaurant_id', array(
            'restaurant_name',
            'restaurant_id' => 'id'
                ), $select::JOIN_INNER);
        $select->join(array(
            'uod' => 'user_order_details'
                ), 'uod.user_order_id = user_orders.id', array(
            'item_id' => 'id',
            'item_name' => 'item'
                ), $select::JOIN_INNER);

        $where = new Where();
        $where->equalTo('user_orders.user_id', $user_id);
        $where->equalTo('user_orders.status', 'archived');
        $where->equalTo('user_orders.is_reviewed', '0');

        $select->where($where);
        $select->order('created_at DESC');
        // var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $resDetail = $this->getDbTable()
                ->setArrayObjectPrototype('ArrayObject')
                ->getReadGateway()
                ->selectWith($select)
                ->toArray();
        return $resDetail;
    }

    public function cancelOrder($id, $userId) {
        $data = array(
            'status' => 'cancelled'
        );

        $writeGateway = $this->getDbTable()->getWriteGateway();
        $dataUpdated = $writeGateway->update($data, array(
            'id' => $id
        ));
        if ($dataUpdated == 0) {
            throw new \Exception("Invalid Order ID provided", 500);
        } else {
            return array(
                'success' => 'true'
            );
        }
    }

    public function getUserArchiveOrderCount($userId, $status, $currentDate) {
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(
            'total_order' => new Expression('COUNT(id)')
        ));
        $where = new Where();
        $where->equalTo('user_id', $userId);
        $where->NEST->NEST->notEqualTo('status', $status)->AND->lessThan('delivery_time', $currentDate)->UNNEST->OR->NEST->EqualTo('status', $status)->UNNEST->UNNEST;
        // $where->lessThan('delivery_time', $currentDate);
        $select->where($where);
        // var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $totalOrder = $this->getDbTable()
                ->setArrayObjectPrototype('ArrayObject')
                ->getReadGateway()
                ->selectWith($select)
                ->current();
        return $totalOrder;
    }

    /**
     * Mob Api Get my order detail
     */
    public function getMyOrderDetail($orderId, $user_id) {
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(
            'order_id' => 'id',
            'customer_first_name' => 'fname',
            'customer_last_name' => 'lname',
            'is_delivery' => 'order_type',
            'is_individual' => 'order_type1',
            'is_personal' => 'order_type2',
            'order_special_instruction' => 'special_checks',
            'order_amount',
            'total_amount',
            'delivery_time',
            'tax',
            'tip_amount',
            'tip_percent',
            'delivery_charge',
            'deal_discount',
            'deal_title',
            'card_number',
            'name_on_card',
            'expired_on',
            'card_type',
            'status',
            'payment_receipt',
            'state_code',
            'city',
            'apt_suite',
            'delivery_address',
            'phone',
            'zipcode',
            'created_at',
            'promocode_discount',
            'billing_zip'
        ));
        $select->join(array(
            'rs' => 'restaurants'
                ), 'rs.id = user_orders.restaurant_id', array(
            'restaurant_name',
            'restaurant_id' => 'id',
            'restaurant_address' => 'address',
            'restaurant_zipcode' => new Expression('rs.zipcode')
                ), $select::JOIN_INNER);
        $select->join(array(
            'c' => 'cities'
                ), 'rs.city_id=c.id', array(
            'city_name',
            'state_code'
                ), $select::JOIN_INNER);
        $select->join(array(
            'uod' => 'user_order_details'
                ), 'uod.user_order_id = user_orders.id', array(
            'item_id' => 'item_id',
            'item_name' => 'item',
            'total_item_amt',
            'item_quantity' => 'quantity',
            'unit_price',
            'special_instruction'
                ), $select::JOIN_INNER);
        $select->join(array(
            'm' => 'menus'
                ), 'uod.item_id=m.id', array(
            'item_status' => 'status'
                ), $select::JOIN_LEFT);
        $select->join(array(
            'ia' => 'user_order_addons'
                ), 'ia.user_order_detail_id=uod.id', array(
            'user_order_detail_id',
            'addons_id' => 'menu_addons_id',
            'addons_name',
            'menu_addons_id',
            'menu_addons_option_id',
            'addons_option',
            'quantity',
            'priority',
            'was_free',
            'addons_price' => 'price'
                ), $select::JOIN_LEFT);

        $where = new Where();
        $where->equalTo('user_orders.user_id', $user_id);
        $where->equalTo('user_orders.id', $orderId);
        $select->where($where);
        //var_dump($select->getSqlString($this->getPlatform('READ')));die();
        $orderDetail = $this->getDbTable()
                ->setArrayObjectPrototype('ArrayObject')
                ->getReadGateway()
                ->selectWith($select)
                ->toArray();
        $myorderDetail = array();
        if ($orderDetail) {
            $order_details_id = array_unique(array_map(function ($i) {
                        return $i['user_order_detail_id'];
                    }, $orderDetail));
            $itemId = array_unique(array_map(function ($i) {
                        return $i['item_id'];
                    }, $orderDetail));

            $orderId = array_unique(array_map(function ($i) {
                        return $i['order_id'];
                    }, $orderDetail));

            $i = 0;
            $response = array();
            foreach ($orderDetail as $key => $value) {

                $response[] = $value;
            }

            $myorderItemDetail = $this->refinegetMyOrderItemDetailForMob($response, $order_details_id);
            $myorderDetail = $this->refinegetMyOrderDetailForMob($response, $orderId);
            $myorderDetail[0]['item_list'] = $myorderItemDetail;
        }
        return $myorderDetail;
    }

    private function refinegetMyOrderItemDetailForMob($userOrders, $order_details_id, $currentTime = NULL) {
        $response = array();
        $orders = array();
        $items = array();
        $orderData = array();
        $index = 0;
        $orderindex = 0;
        $i = 0;
        $userFunctions = new UserFunctions();
        foreach ($order_details_id as $k => $v) {
            $description = '';
            //pr($userOrders,true);
            foreach ($userOrders as $key => $value) {
                if ($v == $value['user_order_detail_id']) {

                    $orders[$value['item_id']][$i]['order_item_id'] = $value['item_id'];
                    $orders[$value['item_id']][$i]['item_name'] = $value['item_name'];
                    $orders[$value['item_id']][$i]['item_qty'] = $value['item_quantity'];
                    $orders[$value['item_id']][$i]['unit_price'] = $value['unit_price'];
                    $orders[$value['item_id']][$i]['item_special_instruction'] = $value['special_instruction'];
                    $orders[$value['item_id']][$i]['item_status'] = ($value['item_status'] == null) ? 0 : (int) $value['item_status'];
                    if (!empty($value['addons_name'])) {
                        $addons = array();
                        $addons['addons_id'] = $value['addons_id'];
                        $addons['addons_name'] = $value['addons_name'];
                        $addons['addons_price'] = $value['addons_price'];
                        $addons['addons_total_price'] = number_format($value['addons_price'] * $value['quantity'], 2);
                        $addons['addon_quantity'] = $value['quantity'];
                        $orders[$value['item_id']][$i]['addons_list'][] = $addons;
                    } elseif (!empty($value['addons_option'])) {
                        $addons = array();
                        $addons['addons_id'] = $value['addons_id'];
                        $addons['addons_name'] = $userFunctions->to_utf8($value['addons_option']); //$value['addons_name'];
                        $addons['addons_price'] = $value['addons_price'];
                        $addons['addons_total_price'] = number_format($value['addons_price'] * $value['quantity'], 2);
                        $addons['addon_quantity'] = $value['quantity'];
                        $orders[$value['item_id']][$i]['addons_list'][] = $addons;
                    } else {
                        $orders[$value['item_id']]['addons_list'] = array();
                    }
                }
            }
            $i ++;
        }

        $key_index = 0;
        foreach ($orders as $key => $orderItem) {
            $orderResponse[$key_index] = $orderItem;
            $key_index ++;
        }

        return $orderResponse;
    }

    private function refinegetMyOrderDetailForMob($userOrders, $orderId, $currentTime = NULL) {
        $response = array();
        $orders = array();
        $items = array();
        $orderData = array();
        $index = 0;
        $orderindex = 0;
        $i = 0;
        foreach ($orderId as $k => $v) {
            $description = '';
            foreach ($userOrders as $key => $value) {
                if ($v == $value['order_id']) {
                    $i ++;
                    $orders[$value['order_id']]['id'] = $value['order_id'];
                    $orders[$value['order_id']]['customer_first_name'] = $value['customer_first_name'];
                    $orders[$value['order_id']]['customer_last_name'] = $value['customer_last_name'];
                    $orders[$value['order_id']]['restaurant_id'] = $value['restaurant_id'];
                    $orders[$value['order_id']]['restaurant_name'] = $value['restaurant_name'];
                    $orders[$value['order_id']]['restaurant_address'] = $value['restaurant_address'] . ", " . $value['city_name'] . ", " . $value['state_code'] . ", " . $value['restaurant_zipcode'];
                    $orders[$value['order_id']]['order_type1'] = $value['is_individual'];
                    $orders[$value['order_id']]['order_type'] = $value['is_delivery'];
                    $orders[$value['order_id']]['order_type2'] = $value['is_personal'];
                    $orders[$value['order_id']]['delivery_date'] = $value['delivery_time'];
                    $orders[$value['order_id']]['order_date'] = $value['created_at'];
                    $orders[$value['order_id']]['special_instruction'] = $value['order_special_instruction'];
                    $orders[$value['order_id']]['status'] = $value['status'];
                    $orders[$value['order_id']]['total_amount'] = number_format($value['total_amount'], 2, '.', '');
                    $orders[$value['order_id']]['tip_percent'] = $value['tip_percent'];
                    $orders[$value['order_id']]['payment_receipt'] = $value['payment_receipt'];

                    $orders[$value['order_id']]['my_delivery_detail']['first_name'] = $value['customer_first_name'];
                    $orders[$value['order_id']]['my_delivery_detail']['last_Name'] = $value['customer_last_name'];
                    $orders[$value['order_id']]['my_delivery_detail']['city'] = $value['city'];
                    $orders[$value['order_id']]['my_delivery_detail']['apt_suite'] = $value['delivery_address'];
                    $orders[$value['order_id']]['my_delivery_detail']['state'] = $value['state_code'];
                    $orders[$value['order_id']]['my_delivery_detail']['phone'] = $value['phone'];
                    $value['delivery_address'] = $value['delivery_address'];

                    if (!empty($value['delivery_address'])) {
                        $address = $value['delivery_address'] . ', ' . $value['city'] . ', ' . $value['state_code'] . ', ' . $value['zipcode'];
                    } else {
                        $address = $value['city'] . ', ' . $value['state_code'] . ', ' . $value['zipcode'];
                    }
                    $orders[$value['order_id']]['my_delivery_detail']['address'] = $address;
                    $orders[$value['order_id']]['my_delivery_detail']['zipcode'] = $value['zipcode'];

                    $orders[$value['order_id']]['my_payment_details']['card_name'] = $value['name_on_card'];
                    $orders[$value['order_id']]['my_payment_details']['card_number'] = $value['card_number'];
                    $orders[$value['order_id']]['my_payment_details']['card_type'] = $value['card_type'];
                    $ex = explode('/', $value['expired_on']);
                    $orders[$value['order_id']]['my_payment_details']['expiry_year'] = $ex[1];
                    $orders[$value['order_id']]['my_payment_details']['expiry_month'] = $ex[0];
                    $orders[$value['order_id']]['my_payment_details']['billing_zip'] = $value['billing_zip'];

                    $subtotal = $value['order_amount'];
                    $orders[$value['order_id']]['order_amount_calculation']['subtotal'] = number_format($subtotal, 2, '.', '');
                    $orders[$value['order_id']]['order_amount_calculation']['tax_amount'] = $value['tax'];
                    $orders[$value['order_id']]['order_amount_calculation']['tip_amount'] = $value['tip_amount'];
                    $orders[$value['order_id']]['order_amount_calculation']['delivery_charge'] = $value['delivery_charge'];
                    $orders[$value['order_id']]['order_amount_calculation']['discount'] = $value['deal_discount'];
                    $orders[$value['order_id']]['order_amount_calculation']['promocode_discount'] = $value['promocode_discount'];
                    $orders[$value['order_id']]['order_amount_calculation']['total_order_price'] = $value['total_amount'];
                    $orders[$value['order_id']]['item_list'] = '';
                }
            }
            $i = 0;
        }

        $key_index = 0;
        foreach ($orders as $key => $orderDetail) {
            $orderDetailResponse[$key_index] = $orderDetail;
            $key_index ++;
        }

        return $orderDetailResponse;
    }

    public function update($data) {
        $this->getDbTable()
                ->getWriteGateway()
                ->update($data, array(
                    'id' => $this->id
        ));
        return true;
    }

    public function isAlreadyOrder(array $options = array()) {
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(
            'id'
        ));

        $select->where(array(
            'restaurant_id' => $options['restaurant_id'],
            'user_id' => $options['user_id']
        ));
        //var_dump($select->getSqlString($this->getPlatform('READ')));
        $response = $this->getDbTable()
                ->setArrayObjectPrototype('ArrayObject')
                ->getReadGateway()
                ->selectWith($select);
        return $response->toArray();
    }

    public function getTotalUserOrders($userId, $status = NULL) {
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(
            'total_order' => new Expression('COUNT(id)')
        ));
        $select->where(array(
            'user_id' => $userId,
            'order_type1' => $status
        ));

        $select->where->notEqualto('order_type', 'Dinein');
        // var_dump($select->getSqlString($this->getPlatform('READ')));

        $totalOrder = $this->getDbTable()
                ->setArrayObjectPrototype('ArrayObject')
                ->getReadGateway()
                ->selectWith($select);
        return $totalOrder->toArray();
    }

    public function getCountUserOrders($userId, $status = NULL) {
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(
            'total_order' => new Expression('COUNT(id)'),
            'user_id'
        ));
        $select->where(array(
            'user_id' => $userId,
            'order_type1' => $status
        ));

        $select->where->notEqualto('order_type', 'Dinein');
        // var_dump($select->getSqlString($this->getPlatform('READ')));

        $totalOrder = $this->getDbTable()
                ->setArrayObjectPrototype('ArrayObject')
                ->getReadGateway()
                ->selectWith($select);
        return $totalOrder->toArray();
    }

    public function getupdateOrderTable($id, $status) {
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(
            'id',
            'delivery_time',
            'status',
            'restaurant_id'
                )
        );

        $where = new Where();

        $where->in('user_orders.status', $status);
        $where->andPredicate(new \Zend\Db\Sql\Predicate\NotIn('user_orders.id ', $id));

        $select->where($where);
        //var_dump($select->getSqlString($this->getPlatform('READ')));die();
        $response = $this->getDbTable()
                ->setArrayObjectPrototype('ArrayObject')
                ->getReadGateway()
                ->selectWith($select);
        return $response->toArray();
    }

    public function getUserArchiveOrderTotalForReview($options = array()) {
        $res = array();

        if (is_numeric($options['limit'])) {

            $limit = $options['limit'];
        }

        $orderBy = 'user_orders.created_at DESC';
        if ($options['orderby'] == 'date') {
            $orderBy = 'user_orders.created_at DESC';
        } elseif ($options['orderby'] == 'amount') {
            $orderBy = 'user_orders.total_amount ASC';
        }
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(
            'total_orders' => new Expression('COUNT(id)'),
            'user_id'
        ));

        /*
         * $select->join(array( 'co' => 'cron_order' ), 'co.order_id = user_orders.id', array( 'archive_time' ), $select::JOIN_LEFT);
         */
        $where = new Where();

        if (!empty($options['group'])) {
            $where->in('user_orders.user_id', $options['userId']);
            $select->group('user_id');
        }

        $where->NEST->notequalTo('user_orders.status', 'placed')->AND->lessThanOrEqualTo('user_orders.delivery_time', $options['currentDate'])->UNNEST;

//    	$where->NEST->equalTo('user_orders.status', 'archived')->
//    	OR->NEST->equalTo('user_orders.status', 'rejected')->AND->
//    	lessThan('user_orders.delivery_time', $options['currentDate'])->UNNEST->
//    	OR->NEST->equalTo('user_orders.status', 'cancelled')->UNNEST->UNNEST;
        // $where->notEqualTo('user_orders.status', $options['orderStatus'][4]);
        $select->where($where);
        $select->order($orderBy);

        ///var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $resDetail = $this->getDbTable()
                ->setArrayObjectPrototype('ArrayObject')
                ->getReadGateway()
                ->selectWith($select)
                ->toArray();

        return $resDetail;
    }

    public function delete() {
        $writeGateway = $this->getDbTable()->getWriteGateway();
        $data = array(
            'status' => $this->status,
            'user_comments' => $this->user_comments,
        );
        $rowsAffected = $writeGateway->update($data, array('id' => $this->id));
        return $rowsAffected;
    }

    public function updateMistryMealOrder($data) {
        $this->getDbTable()->getWriteGateway()->update($data, array('email' => $this->email));
        return true;
    }

    public function getMyOrderDetailForReorder($orderId, $user_id) {
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(
            'order_id' => 'id',
            'customer_first_name' => 'fname',
            'customer_last_name' => 'lname',
            'is_delivery' => 'order_type',
            'is_individual' => 'order_type1',
            'is_personal' => 'order_type2',
            'order_special_instruction' => 'special_checks',
            'total_amount',
            'delivery_time',
            'tax',
            'tip_amount',
            'tip_percent',
            'delivery_charge',
            'deal_discount',
            'card_number',
            'name_on_card',
            'expired_on',
            'card_type',
            'status',
            'payment_receipt',
            'state_code',
            'city',
            'apt_suite',
            'phone',
            'zipcode',
            'created_at',
            'promocode_discount',
            'billing_zip',
            'street' => 'address',
            'latitude',
            'longitude'
        ));
        $select->join(array(
            'rs' => 'restaurants'
                ), 'rs.id = user_orders.restaurant_id', array(
            'restaurant_name',
            'rest_code',
            'delivery_area',
            'delivery_charge',
            'restaurant_id' => 'id',
            'restaurant_address' => 'address',
            'reservations',
            'dining',
            'accept_cc_phone',
            'takeout',
            'minimum_delivery_amount' => 'minimum_delivery',
            'menu_available',
            'menu_without_price',
            'restaurant_zipcode' => new Expression('rs.zipcode'),
            'delivery',
                ), $select::JOIN_INNER);
        $select->join(array(
            'c' => 'cities'
                ), 'rs.city_id=c.id', array(
            'city_name',
            'state_code',
            'sales_tax',
                ), $select::JOIN_INNER);
        $select->join(array(
            'uod' => 'user_order_details'
                ), 'uod.user_order_id = user_orders.id', array(
            'id',
            'item_name' => 'item',
            'total_item_amt',
            'quantity',
            'unit_price',
            'special_instruction',
            'item_id',
            'item_price_id',
            'item_price_desc'
                ), $select::JOIN_INNER);
        $select->join(array(
            'm' => 'menus'
                ), 'uod.item_id=m.id', array(
            'item_status' => 'status',
                ), $select::JOIN_INNER);

        $select->join(array(
            'ia' => 'user_order_addons'
                ), 'ia.user_order_detail_id=uod.id', array(
            'addons_id' => 'menu_addons_id',
            'addons_name',
            'menu_addons_id',
            'menu_addons_option_id',
            'addons_option',
            'priority',
            'was_free',
            'addons_price' => 'price'
                ), $select::JOIN_LEFT);

        $where = new Where();
        $where->equalTo('user_orders.user_id', $user_id);
        $where->equalTo('user_orders.id', $orderId);
        $select->where($where);

        // var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $orderDetail = $this->getDbTable()
                ->setArrayObjectPrototype('ArrayObject')
                ->getReadGateway()
                ->selectWith($select)
                ->toArray();
        $myorderDetail = array();
        if ($orderDetail) {
            $itemId = array_unique(array_map(function ($i) {
                        return $i['item_id'];
                    }, $orderDetail));

            $orderId = array_unique(array_map(function ($i) {
                        return $i['order_id'];
                    }, $orderDetail));

            $i = 0;
            $response = array();
            foreach ($orderDetail as $key => $value) {

                $response[] = $value;
            }

            $myorderItemDetail = $this->refinegetMyOrderItemDetailForMobReOrder($response, $itemId);
            $myorderDetail = $this->refinegetMyOrderDetailForMobReOrder($response, $orderId, $user_id);

            $myorderDetail[0]['item_list'] = $myorderItemDetail;
        }
        return $myorderDetail;
    }

    private function refinegetMyOrderItemDetailForMobReOrder($userOrders, $itemId, $currentTime = NULL) {
        $response = array();
        $orders = array();
        $items = array();
        $orderData = array();
        $orderResponse = array();
        $index = 0;
        $orderindex = 0;
        $i = 0;
        $userFunctions = new UserFunctions();
        $addonsId = array();
        foreach ($itemId as $k => $v) {
            $description = '';

            foreach ($userOrders as $key => $value) {
                if ($value['item_status'] > 0) {
                    if ($v == $value['item_id']) {
                        $i ++;

                        $orders[$value['item_id']]['order_item_id'] = $value['id'];
                        //$orders[$value['item_id']]['item_id'] = $value['item_id'];
                        //$orders[$value['item_id']]['item_name'] = $value['item_name'];
                        //$orders[$value['item_id']]['item_qty'] = $value['quantity'];
                        //$orders[$value['item_id']]['item_special_instruction'] = $value['special_instruction'];
                        $orders[$value['item_id']]['item_status'] = $value['item_status'];
                        // $orders[$value['item_id']]['item_price_id'] = $value['item_price_id'];
                        // $orders[$value['item_id']]['item_price_desc'] = $value['item_price_desc'];
                        $orders[$value['item_id']]['category_items'] = $this->MenuPrice($value['item_id'], $value['item_price_id']);
                        $orders[$value['item_id']]['category_items']['item_qty'] = $value['quantity'];
                        $addonId[] = $value['addons_id'];
                        $menuAddons = $this->menuAddons($value['item_id'], $value['item_price_id'], $addonId);
                        $orders[$value['item_id']]['data'] = $menuAddons;
                    }
                }
            }
            $i = 0;
        }

        $key_index = 0;
        foreach ($orders as $key => $orderItem) {
            $orderResponse[$key_index] = $orderItem;
            $key_index ++;
        }
        return $orderResponse;
    }

    private function MenuPrice($menuId, $itemPriceId) {
        $menuPrice = new \Restaurant\Model\MenuPrices();
        $menuPrice->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $joins = array();
        $joins [] = array(
            'name' => array(
                'm' => 'menus'
            ),
            'on' => 'm.id = menu_prices.menu_id',
            'columns' => array(
                'item_id' => new \Zend\Db\Sql\Predicate\Expression('m.id'),
                'item_name',
                'item_image_url' => 'image_name',
                'item_desc',
                'online_order_allowed',
            ),
            'type' => 'left'
        );
        $options = array(
            'columns' => array('id', 'value' => 'price', 'desc' => 'price_desc'),
            'where' => array('menu_id' => $menuId),
            'joins' => $joins
        );
        $menuPriceDetails = $menuPrice->find($options)->toArray();
        $categoryItems = array();
        foreach ($menuPriceDetails as $key => $value) {
            $categoryItems['online_order_allowed'] = $value['online_order_allowed'];
            if ($itemPriceId == $value['id']) {
                $prices['status'] = true;
            } else {
                $prices['status'] = false;
            }
            $prices['id'] = $value['id'];
            $prices['value'] = $value['value'];
            $prices['desc'] = $value['desc'];
            $categoryItems['prices'][] = $prices;
            $categoryItems['item_id'] = $value['item_id'];
            $categoryItems['item_image_url'] = $value['item_image_url'];
            $categoryItems['item_name'] = $value['item_name'];
            $categoryItems['item_desc'] = $value['item_desc'];
        }
        return $categoryItems;
    }

    public function menuAddons($menuId, $itempriceId, $addonsId, $selectionType = false) {
        $menuModel = new \Restaurant\Model\Menu();
        $menuModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $joins = array();
        $joins [] = array(
            'name' => array(
                'ma' => 'menu_addons'
            ),
            'on' => 'menus.id = ma.menu_id',
            'columns' => array(
                'addon_option',
                'addon_main_id' => 'id',
                'addon_id',
                'menu_price_id',
                'addon_price' => 'price',
                'addon_price_description' => 'price_description',
                'addon_description' => 'description',
                'selection_type',
            ),
            'type' => 'left'
        );
        $joins [] = array(
            'name' => array(
                'mas' => 'menu_addon_settings'
            ),
            'on' => new \Zend\Db\Sql\Expression('ma.addon_id = mas.addon_id AND mas.menu_id = menus.id'),
            'columns' => array(
                'item_limit',
                'enable_pricing_beyond'
            ),
            'type' => 'inner'
        );
         $joins [] = array(
            'name' => array(
                'mp' => 'menu_prices'
            ),
            'on' => 'ma.menu_price_id = mp.id',
//            'on' => new \Zend\Db\Sql\Expression("uod.item_id=mp.menu_id and uod.item_price_id=mp.id"),
            'columns' => array(
                'price',
                'menu_price_id' => 'id'
            ),
            'type' => 'inner'
        );
        
         $joins [] = array(
            'name' => array(
                'uod' => 'user_order_details'
            ),
            'on' => new \Zend\Db\Sql\Expression("uod.item_id=mp.menu_id and uod.item_price_id=mp.id"),
            'columns' => array(
                'item_id',
                'item_price_desc'
            ),
            'type' => 'inner'
        );
         
         $joins [] = array(
            'name' => array(
                'uoa' => 'user_order_addons'
            ),
            'on' => new \Zend\Db\Sql\Expression("uoa.menu_addons_option_id=ma.id"),
            'columns' => array(
                'addons_name'                    
            ),
            'type' => 'left'
        );        
        
        
         
        $joins [] = array(
            'name' => array(
                'a' => 'addons'
            ),
            'on' => 'a.id = ma.addon_id',
            'columns' => array(
                'addon_name'
            ),
            'type' => 'inner'
        );
        $options = array(
            'columns' => array(
                'item_name',
                'item_desc'
            ),
            'where' => array(
                'menus.id' => $menuId,
                'menus.status' => 1,
                'mp.id'=>$itempriceId
            ),
            'joins' => $joins
        );
        $response = $menuModel->find($options)->toArray();
        $refined = array();
//        pr($addonsId);
//        pr($response);
//        die;
        foreach ($response as $key => $value) {

            if (!isset($refined [$response [$key] ['menu_price_id']])) {
                $refined [$response [$key] ['menu_price_id']] = array();
            }
            if (!isset($refined [$response [$key] ['menu_price_id']] [$response [$key] ['addon_id']])) {
                $refined [$response [$key] ['menu_price_id']] [$response [$key] ['addon_id']] = array();
                $refined [$response [$key] ['menu_price_id']] [$response [$key] ['addon_id']] ['name'] = $response [$key] ['addon_name'];
                $refined [$response [$key] ['menu_price_id']] [$response [$key] ['addon_id']] ['addon_id'] = $response [$key] ['addon_id'];
            }
            $refined [$response [$key] ['menu_price_id']] [$response [$key] ['addon_id']]['selection_type'] = $response[$key]['selection_type'];
            $refined [$response [$key] ['menu_price_id']] [$response [$key] ['addon_id']]['item_limit'] = $response[$key]['item_limit'];
            $refined [$response [$key] ['menu_price_id']] [$response [$key] ['addon_id']]['enable_pricing_beyond'] = $response[$key]['enable_pricing_beyond'];
            if ($response[$key]['enable_pricing_beyond'] != '') {
                $refined [$response [$key] ['menu_price_id']] [$response [$key] ['addon_id']]['item_limit'] = '';
            }
           
            if (!isset($refined [$response [$key] ['menu_price_id']] [$response [$key] ['addon_id']] ['options'] [$response[$key]['addon_main_id']])) {
                if (in_array($value['addon_main_id'], $addonsId)) {
                    $response[$key]['status'] = true;                  
                } else {
                    $response[$key]['status'] = false;
                }
                
                $refined [$response [$key] ['menu_price_id']] [$response [$key] ['addon_id']] ['options'] [$response[$key]['addon_main_id']] = array(
                      'id' => $response[$key]['addon_main_id'],
                      'name' => $response [$key] ['addon_option'],
                      'addon_description' => $response [$key] ['addon_description'],
                      'price' => $response [$key] ['addon_price'],
                      'description' => $response [$key] ['addon_price_description'],
                      'status' => $response[$key]['status'],
                      'slection_type'=>$response[$key]['selection_type'],
                  );
               
                
            }
        }

        $final = array();
//        pr($refined,1);
        foreach ($refined as $key => $value) {
            foreach ($value as $subKey => $subValue) {
                $value[$subKey]['options'] = array_values($value[$subKey]['options']);
            }
            $final [] = array(
                'menu_price_id' => $key,
                'addons' => array_values($value)
            );
        }
        return $final;
    }

    private function refinegetMyOrderDetailForMobReOrder($userOrders, $orderId, $userId = false, $currentTime = NULL) {
        $response = array();
        $orders = array();
        $items = array();
        $orderData = array();
        $index = 0;
        $orderindex = 0;
        $i = 0;
        foreach ($orderId as $k => $v) {
            $description = '';
            foreach ($userOrders as $key => $value) {
                if ($v == $value['order_id']) {
                    $i ++;
                    $orders[$value['order_id']]['id'] = $value['order_id'];
                    $orders[$value['order_id']]['customer_first_name'] = $value['customer_first_name'];
                    $orders[$value['order_id']]['customer_last_name'] = $value['customer_last_name'];
                    $orders[$value['order_id']]['restaurant_id'] = $value['restaurant_id'];
                    $orders[$value['order_id']]['restaurant_name'] = $value['restaurant_name'];
                    $orders[$value['order_id']]['restaurant_address'] = $value['restaurant_address'] . ", " . $value['city_name'] . ", " . $value['state_code'] . ", " . $value['restaurant_zipcode'];
                    $orders[$value['order_id']]['order_type1'] = $value['is_individual'];
                    $orders[$value['order_id']]['order_type'] = $value['is_delivery'];
                    $orders[$value['order_id']]['order_type2'] = $value['is_personal'];
                    $orders[$value['order_id']]['delivery_date'] = $value['delivery_time'];
                    $orders[$value['order_id']]['order_date'] = $value['created_at'];
                    $orders[$value['order_id']]['special_instruction'] = $value['order_special_instruction'];
                    $orders[$value['order_id']]['status'] = $value['status'];
                    $orders[$value['order_id']]['total_amount'] = number_format($value['total_amount'], 2, '.', '');
                    $orders[$value['order_id']]['tip_percent'] = $value['tip_percent'];
                    $orders[$value['order_id']]['payment_receipt'] = $value['payment_receipt'];

                    $orders[$value['order_id']]['my_delivery_detail']['first_name'] = $value['customer_first_name'];
                    $orders[$value['order_id']]['my_delivery_detail']['last_name'] = $value['customer_last_name'];
                    $orders[$value['order_id']]['my_delivery_detail']['city'] = $value['city'];
                    $orders[$value['order_id']]['my_delivery_detail']['apt_suite'] = $value['apt_suite'];
                    $orders[$value['order_id']]['my_delivery_detail']['street'] = $value['street'];
                    $orders[$value['order_id']]['my_delivery_detail']['state'] = $value['state_code'];
                    $orders[$value['order_id']]['my_delivery_detail']['phone'] = $value['phone'];
                    $orders[$value['order_id']]['my_delivery_detail']['address'] = $value['city'] . ', ' . $value['state_code'];
                    $orders[$value['order_id']]['my_delivery_detail']['zipcode'] = $value['zipcode'];
                    $orders[$value['order_id']]['my_delivery_detail']['latitude'] = $value['latitude'];
                    $orders[$value['order_id']]['my_delivery_detail']['longitude'] = $value['longitude'];

                    $orders[$value['order_id']]['my_payment_details']['card_name'] = $value['name_on_card'];
                    $orders[$value['order_id']]['my_payment_details']['card_number'] = $value['card_number'];
                    $orders[$value['order_id']]['my_payment_details']['card_type'] = $value['card_type'];
                    $ex = explode('/', $value['expired_on']);
                    $orders[$value['order_id']]['my_payment_details']['expiry_year'] = $ex[1];
                    $orders[$value['order_id']]['my_payment_details']['expiry_month'] = $ex[0];
                    $orders[$value['order_id']]['my_payment_details']['billing_zip'] = $value['billing_zip'];

                    $subtotal = $value['total_amount'] - $value['tax'] - $value['tip_amount'] - $value['delivery_charge'];
                    $orders[$value['order_id']]['order_amount_calculation']['subtotal'] = number_format($subtotal, 2, '.', '');
                    $orders[$value['order_id']]['order_amount_calculation']['tax_amount'] = $value['tax'];
                    $orders[$value['order_id']]['order_amount_calculation']['tip_amount'] = $value['tip_amount'];
                    $orders[$value['order_id']]['order_amount_calculation']['delivery_charge'] = $value['delivery_charge'];
                    $orders[$value['order_id']]['order_amount_calculation']['discount'] = $value['deal_discount'];
                    $orders[$value['order_id']]['order_amount_calculation']['total_order_price'] = $value['total_amount'];
                    $orders[$value['order_id']]['item_list'] = '';

                    $orders[$value['order_id']]['restaurant']['name'] = $value['restaurant_name'];
                    $orders[$value['order_id']]['restaurant']['rest_code'] = strtolower($value['rest_code']);
                    $orders[$value['order_id']]['restaurant']['deal'] = "";
                    $orders[$value['order_id']]['restaurant']['delivery_charge'] = $value['delivery_charge'];
                    $orders[$value['order_id']]['restaurant']['delivery_area'] = $value['delivery_area'];
                    $orders[$value['order_id']]['restaurant']['tax_percentage'] = $value['sales_tax'];
                    $orders[$value['order_id']]['restaurant']['is_running_deal_coupon'] = "";
                    $orders[$value['order_id']]['restaurant']['is_reservation'] = $value['reservations'];
                    $orders[$value['order_id']]['restaurant']['is_dining'] = $value['dining'];
                    $orders[$value['order_id']]['restaurant']['is_accept_cc'] = $value['accept_cc_phone'];
                    $orders[$value['order_id']]['restaurant']['is_register'] = "";
                    $orders[$value['order_id']]['restaurant']['preordering_enabled'] = "";
                    $orders[$value['order_id']]['restaurant']['is_delivery'] = $value['delivery'];
                    $orders[$value['order_id']]['restaurant']['minimum_delivery_amount'] = $value['minimum_delivery_amount'];
                    $orders[$value['order_id']]['restaurant']['is_takeout'] = $value['takeout'];
                    $orders[$value['order_id']]['restaurant']['menu_available'] = $value['menu_available'];
                    $orders[$value['order_id']]['restaurant']['menu_without_price'] = $value['menu_without_price'];
                }
            }
            $i = 0;
        }

        $key_index = 0;
        foreach ($orders as $key => $orderDetail) {
            $orderDetailResponse[$key_index] = $orderDetail;
            $key_index ++;
        }

        return $orderDetailResponse;
    }

    public function getOrderReview($orderId = false) {
        $review = 0;
        if ($orderId) {
            $userReviewModel = new \User\Model\UserReview();

            $options = array(
                'columns' => array(
                    'review_id' => 'id'
                ),
                'where' => array(
                    'order_id' => $orderId,
                    'status' => array(0, 1, 2)
                ),
            );
            $userReviewModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
            $reviewed = $userReviewModel->find($options)->toArray();
            if ($reviewed) {
                $review = array('is_review' => 1, 'review_id' => $reviewed[0]['review_id']);
            }
        }
        return $review;
    }

    public function getUserConfirmOrder($user = false) {
        $options = array('columns' => array('id', 'restaurant_id'), 'where' => array('user_id' => $user, 'status' => 'confirmed', 'assignMuncher' => '0'));
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $userOrder = $this->find($options)->toArray();
        return $userOrder;
    }

    public function getUserConfirmDeliveryOrder($user = false) {
        $options = array('columns' => array('restaurant_id'), 'where' => array('user_id' => $user, 'status' => 'confirmed', 'order_type' => 'Delivery', 'assignMuncher' => '0'));
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $userOrder = $this->find($options)->toArray();
        return $userOrder;
    }

    public function getUserConfirmTakeoutOrder($user = false) {
        $options = array('columns' => array('restaurant_id'), 'where' => array('user_id' => $user, 'status' => 'confirmed', 'order_type' => 'Takeout', 'assignMuncher' => '0'));
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $userOrder = $this->find($options)->toArray();
        return $userOrder;
    }

    public function getTotalUserFirstOrders($userId, $restaurantId = false) {
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(
            'total_order' => new Expression('COUNT(id)')
        ));
        $where = new Where ();
        if($restaurantId){
            $where->equalTo('user_id', $userId)->AND->equalTo('restaurant_id', $restaurantId)->AND->notEqualto('order_type', 'Dinein')->AND->notequalTo('status', 'cancelled')->AND->notEqualTo('status', 'rejected')->AND->notEqualTo('status', 'ordered')->AND->notEqualTo('status', 'placed');
        }else{
            $where->equalTo('user_id', $userId)->AND->notEqualto('order_type', 'Dinein')->AND->notequalTo('status', 'cancelled')->AND->notEqualTo('status', 'rejected')->AND->notEqualTo('status', 'ordered')->AND->notEqualTo('status', 'placed');
        }
        $select->where($where);
        //pr($select->getSqlString($this->getPlatform('READ')),true);
        $totalOrder = $this->getDbTable()
                ->setArrayObjectPrototype('ArrayObject')
                ->getReadGateway()
                ->selectWith($select);

        return $totalOrder->toArray();
    }

    public function updateCronOrder($id = false) {
        $this->getDbTable()->getWriteGateway()->update(array('cronUpdate' => 1), array(
            'id' => $id
        ));
        return true;
    }

    public function updateCronNotification($id = false) {
        $this->getDbTable()->getWriteGateway()->update(array('cronUpdateNotification' => 1), array(
            'id' => $id
        ));
        return true;
    }

    public function updateMuncher($data) {
        $this->getDbTable()->getWriteGateway()->update($data, array(
            'id' => $this->id
        ));
        return true;
    }

    public function getTotalPlacedOrder($userId) {
        $select = new Select();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array('total_order' => new Expression('COUNT(id)')));
        $where = new Where();
        $where->NEST->in('status', array('placed', 'ordered', 'confirmed', 'delivered', 'arrived', 'archived', 'rejected', 'cancelled'))->UNNSET->OR->NEST->equalTo('order_type', 'Dinein')->UNNEST->UNNEST->AND->equalTo('user_id', $userId);
        $select->where($where);
        $totalOrder = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->current();
        return (int) $totalOrder['total_order'];
    }

    public function updateCronPreOrder($id = false) {
        $this->getDbTable()->getWriteGateway()->update(array('cronsmsupdate' => 1), array(
            'id' => $id
        ));
        return true;
    }

    public function getTotalOrdersWithDinein($userId, $status = NULL) {
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(
            'total_order' => new Expression('COUNT(id)'),
            'user_id'
        ));
        $select->where(array(
            'user_id' => $userId,
            'order_type1' => $status
        ));

        $totalOrder = $this->getDbTable()
                ->setArrayObjectPrototype('ArrayObject')
                ->getReadGateway()
                ->selectWith($select);
        return $totalOrder->toArray();
    }

    public function getArchiveOrderForNotification($oId = false, $current_date = false) {
        $res = array();
        $totalArchiveRecords = 0;

        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(
            'id'
        ));
        $where = new Where();
        $where->equalTo('id', $oId);
        $where->notEqualTo('order_type', 'Dinein');
        $where->NEST->equalTo('status', 'archived')->
                        OR->NEST->equalTo('status', 'rejected')->AND->
                        lessThan('delivery_time', $current_date)->UNNEST->
                OR->NEST->equalTo('status', 'cancelled')->UNNEST->UNNEST;
        $select->where($where);
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $resDetail = $this->getDbTable()
                ->setArrayObjectPrototype('ArrayObject')
                ->getReadGateway()
                ->selectWith($select)
                ->toArray();
        return $resDetail;
    }

    public function getConfirmOrder($userId, $restaurantId) {
        $options = array('columns' => array('id', 'restaurant_id'), 'where' => array('user_id' => $userId, 'restaurant_id' => $restaurantId, 'status' => 'confirmed'));
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $userOrder = $this->find($options)->toArray();
        return $userOrder;
    }

    public function getUserfirstOrder($userId, $restId, $startDate, $endDate) {
        $status = array('confirmed', 'delivered', 'arrived', 'archived');
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(
            'created_at'
        ));
        $select->where(array(
            'user_id' => $userId,
            'restaurant_id' => $restId
        ));
        $where = new Where ();
        $where->equalTo('user_id', $userId);
        $where->equalTo('restaurant_id', $restId);
        $where->in('status', $status);
        $where->between('created_at', $startDate, $endDate);
        $select->where($where);
        $select->order('created_at ASC');
        $select->limit(1);
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $firstorder = $this->getDbTable()
                ->setArrayObjectPrototype('ArrayObject')
                ->getReadGateway()
                ->selectWith($select);
        $order = $firstorder->toArray();
        if (empty($order)) {
            return '';
        } else {
            $date = date_create(substr($order[0]['created_at'], 0, 10));
            return date_format($date, "m/d/Y");
        }
    }

    public function getRestaurantOrders($restId, $startDate, $endDate) {
        $status =  array('confirmed', 'delivered', 'arrived', 'archived', 'cancelled', 'rejected');
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(
            'restaurant_id',
            'user_id',
            'order_type',
            'total_amount',
            'status',
        ));
        $where = new Where ();
        $where->equalTo('user_orders.restaurant_id', $restId);
        $where->in('user_orders.status', $status);
        $where->between('user_orders.created_at', $startDate, $endDate);
        $select->where($where);
        $select->order('user_orders.created_at DESC');
        //var_dump($select->getSqlString($this->getPlatform('READ')));//die;
        $data = $this->getDbTable()
                        ->setArrayObjectPrototype('ArrayObject')
                        ->getReadGateway()
                        ->selectWith($select)->toArray();
        if (empty($data)) {
            return '';
        } else {
            return $data;
        }
    }

    public function getRestaurantTotalOrdersAndRevenu($restId,$restStartDate,$restEndDate) {
        $status = array('confirmed', 'delivered', 'arrived', 'archived','cancelled','rejected');
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(
            'restaurant_id',
            'total_orders' => new Expression('COUNT(id)'),
            'total_revenue' => new Expression('SUM(total_amount)'),
        ));
        $where = new Where ();
        $where->equalTo('restaurant_id', $restId);
        $where->in('status', $status);
        $where->between('created_at', $restStartDate, $restEndDate);
        $select->where($where);
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;//die;
        $data = $this->getDbTable()
                        ->setArrayObjectPrototype('ArrayObject')
                        ->getReadGateway()
                        ->selectWith($select)->toArray();
        if (empty($data)) {
            return '';
        } else {
            return $data[0];
        }
    }
    public function getRestaurantTotalSuccessOrders($restId,$restStartDate,$restEndDate) {
        $status = array('confirmed', 'delivered', 'arrived', 'archived');
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(
            'success_orders' => new Expression('COUNT(id)'),
        ));
        $where = new Where ();
        $where->equalTo('restaurant_id', $restId);
        $where->in('status', $status);
        $where->between('created_at', $restStartDate, $restEndDate);
        $select->where($where);
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $data = $this->getDbTable()
                        ->setArrayObjectPrototype('ArrayObject')
                        ->getReadGateway()
                        ->selectWith($select)->toArray();
        if (empty($data)) {
            return '';
        } else {
            return $data[0]['success_orders'];
        }
    }
    

    public function getRestaurantTakeoutVSDelivery($restId,$restStartDate,$restEndDate) {
        $status = array('confirmed', 'delivered', 'arrived', 'archived','cancelled','rejected');
        $orderType = array('Takeout', 'Delivery');
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(
            'restaurant_id',
            'id',
            'order_type',
        ));
        $where = new Where ();
        $where->equalTo('restaurant_id', $restId);
        $where->in('status', $status);
        $where->in('order_type', $orderType);
        $where->between('created_at', $restStartDate, $restEndDate);
        $select->where($where);
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $data = $this->getDbTable()
                        ->setArrayObjectPrototype('ArrayObject')
                        ->getReadGateway()
                        ->selectWith($select)->toArray();
        $takeoutcnt = 0;
        $deliverycnt = 0;
        $orderType = [];
        if (!empty($data)) {
            foreach ($data as $value) {
                if ($value['order_type'] == 'Takeout') {
                    $takeoutcnt ++;
                } else {
                    $deliverycnt ++;
                }
            }
        } 
        $orderType['takeout'] = $takeoutcnt;
        $orderType['delivery'] = $deliverycnt;
        return $orderType;
    }
    public function getRestaurantNewVSReturningCustomers($restId,$restStartDate,$restEndDate) {
        $status = array('confirmed', 'delivered', 'arrived', 'archived','cancelled','rejected');
        $orderType = array('Takeout', 'Delivery');
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(
            'restaurant_id',
            'total_orders' => new Expression('COUNT(id)'),
        ));
        $where = new Where ();
        $where->equalTo('restaurant_id', $restId);
        //$where->in('restaurant_id', $restIds);
        $where->in('status', $status);
        $where->in('order_type', $orderType);
        $where->between('created_at', $restStartDate, $restEndDate);
        $select->where($where);
        $select->group('email');
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $data = $this->getDbTable()
                        ->setArrayObjectPrototype('ArrayObject')
                        ->getReadGateway()
                        ->selectWith($select)->toArray();
        $newCustomers = 0;
        $retuCustomers = 0;
        $customerType = [];
        if (!empty($data)) {
            foreach ($data as $value) {
                if ($value['total_orders'] > 1) {
                    $retuCustomers ++;
                } else {
                    $newCustomers ++;
                }
            }
        } 
        $customerType['total_customers'] = $newCustomers + $retuCustomers;
        $customerType['new_customers'] = $newCustomers;
        $customerType['returning_customers'] = $retuCustomers;
        return $customerType;
    }
    public function getUserTotalOrders($userId,$restId,$restStartDate,$restEndDate) {
        $status = array('confirmed', 'delivered', 'arrived', 'archived','cancelled','rejected');
        $orderType = array('Takeout', 'Delivery');
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(
            'total_orders' => new Expression('COUNT(id)'),
        ));
        $where = new Where ();
        $where->equalTo('user_id', $userId);
        $where->equalTo('restaurant_id', $restId);
        $where->in('status', $status);
        $where->in('order_type', $orderType);
        $where->between('created_at', $restStartDate, $restEndDate);
        $select->where($where);
        $select->group('user_id');
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $data = $this->getDbTable()
                        ->setArrayObjectPrototype('ArrayObject')
                        ->getReadGateway()
                        ->selectWith($select)->toArray();
        if (!empty($data)) {
            return $data[0]['total_orders'];
        }else{
            return 0;
        } 
        
    }
    public function getRestaurantDineAndMoreRevenue($restId,$restStartDate,$restEndDate) {
        $status = array('confirmed', 'delivered', 'arrived', 'archived','cancelled','rejected');
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(
            'total_orders' => new Expression('COUNT(user_orders.id)'),
            'total_revenue' => new Expression('SUM(total_amount)'),
        ));
        $select->join(array(
            'rs' => 'restaurant_servers'
                ), 'rs.user_id = user_orders.user_id and rs.restaurant_id = user_orders.restaurant_id', array(
                    'total_users_orders_members' => new Expression('COUNT(rs.user_id)'),
                ), $select::JOIN_INNER);
        $where = new Where ();
        $where->equalTo('user_orders.restaurant_id', $restId);
        $where->in('user_orders.status', $status);
        $where->between('user_orders.created_at', $restStartDate, $restEndDate);
        $select->where($where);
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $data = $this->getDbTable()
                        ->setArrayObjectPrototype('ArrayObject')
                        ->getReadGateway()
                        ->selectWith($select)->toArray();
        if (empty($data)) {
            return 0;
        } else {
            return $data[0];
        }
    }
    public function getRestaurantDineAndMoreRevenueDaily($restId,$startDate,$endDate) {
        $status = array('confirmed', 'delivered', 'arrived', 'archived');
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(
            'total_orders' => new Expression('COUNT(user_orders.id)'),
            'total_revenue' => new Expression('SUM(total_amount)'),
        ));
        $select->join(array(
            'rs' => 'restaurant_servers'
                ), 'rs.user_id = user_orders.user_id', array(
                    'total_users_orders_members' => new Expression('COUNT(rs.user_id)'),
                ), $select::JOIN_INNER);
        $where = new Where ();
        $where->equalTo('user_orders.restaurant_id', $restId);
        $where->in('user_orders.status', $status);
        $where->between('user_orders.created_at', $startDate, $endDate);
        $select->where($where);
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $data = $this->getDbTable()
                        ->setArrayObjectPrototype('ArrayObject')
                        ->getReadGateway()
                        ->selectWith($select)->toArray();
        if (empty($data)) {
            return 0;
        } else {
            return $data[0];
        }
    }
    public function getRestaurantNormalUsersRevenue($restId,$restStartDate,$restEndDate) {
        $status = array('confirmed', 'delivered', 'arrived', 'archived');
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(
            'total_orders' => new Expression('COUNT(user_orders.id)'),
            'total_revenue' => new Expression('SUM(total_amount)'),
        ));
        $select->join(array(
            'rs' => 'restaurant_servers'
                ), new Expression('rs.user_id <> user_orders.user_id and rs.restaurant_id = user_orders.restaurant_id'), array(
                    'total_users_orders_normal_users' => new Expression('COUNT(rs.user_id)'),
                ), $select::JOIN_INNER);
        $where = new Where ();
        $where->equalTo('user_orders.restaurant_id', $restId);
        $where->in('user_orders.status', $status);
        $where->between('user_orders.created_at', $restStartDate, $restEndDate);
        $select->where($where);
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $data = $this->getDbTable()
                        ->setArrayObjectPrototype('ArrayObject')
                        ->getReadGateway()
                        ->selectWith($select)->toArray();
        if (empty($data)) {
            return 0;
        } else {
            return $data[0];
        }
    }
     public function getRestaurantOrderIds($restId, $restStartDate, $restEndDate) {
        $status = array('confirmed', 'delivered', 'arrived', 'archived', 'cancelled', 'rejected');
        $orderType = array('Takeout', 'Delivery');
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(
            'id',
        ));
        $where = new Where ();
        $where->equalTo('restaurant_id', $restId);
        $where->in('status', $status);
        $where->in('order_type', $orderType);
        $where->between('created_at', $restStartDate, $restEndDate);
        $select->where($where);
        //var_dump($select->getSqlString($this->getPlatform('READ')));//die;
        $orderIds = $this->getDbTable()
                        ->setArrayObjectPrototype('ArrayObject')
                        ->getReadGateway()
                        ->selectWith($select)->toArray();
        $ids = [];
        if (!empty($orderIds)) {
            foreach ($orderIds as $key => $value) {
                $ids[] = $value['id'];
            }
        }else{
             $ids = [0];
        }
        return $ids;
        
    }
    
    public function orderStatus($orderid){
        $options = array('columns' => array('status'), 'where' => array('id' => $orderid));
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $userOrder = $this->find($options)->toArray();
        return $userOrder;
    }

}
