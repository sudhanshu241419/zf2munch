<?php

namespace Dashboard\Controller;

use MCommons\Controller\AbstractRestfulController;
use Dashboard\DashboardFunctions;
use Dashboard\Model\DashboardOrder;

class DashboardOrderController extends AbstractRestfulController {

    public $restaurantId;
    public $restaurantAddress;
    public $orderId;
    public $deliveryTime;
    public $location;
    public $customerCall;
    public $currentDate;
    public $userId;
    public $isUserDineAndMore = false;
    public $isFirstOrder = false;
    public $orderPoint = 0;
    public $orderby = "date";
    public $page = 1;
    public $limit = 50;
    public $type = "live";
    public $offset = 0;
    public $hostName;
    public $restaurantLogo;
    public $orderStatus;
    public $cancelReason = false;
    public $fromDate = 1;
    public $toDate = 1;
    public function getList() {        
        $dashboardFunctions = new DashboardFunctions();
        $userAgent = \MCommons\StaticOptions::getUserAgent();//ws,android,iOS        
        $this->restaurantId = $dashboardFunctions->getRestaurantId();
        $this->orderby = $this->getQueryParams('orderby', 'date');
        $this->page = $this->getQueryParams('page', 1);
        $this->limit = $this->getQueryParams('limit', SHOW_PER_PAGE);
        $this->type = $this->getQueryParams('type');
        $this->fromDate = $this->getQueryParams('fromDate', false);
        $this->toDate = $this->getQueryParams('toDate', false);
        $dashboardFunctions->getLocation();
        $this->currentDate = $dashboardFunctions->CityTimeZone();
              
        if ($this->page > 0) {
            $this->page = ($this->page < 1) ? 1 : $this->page;
            $this->offset = ($this->page - 1) * ($this->limit);
        }
        
        if ($this->type === "live" && $userAgent === "ws") {
           return $this->liveOrderWs();            
        }elseif($this->type === "archive" && $userAgent === "ws"){
           return $this->archiveOrderWs(); 
        }
            
        if ($this->type === "archive" && $userAgent === "android") {
            return $this->archiveOrderMob();            
        }
        if($this->type === "live" && $userAgent === "android"){
            return $this->liveOrderMob();
        }
        if ($this->type === "all" && !empty($this->fromDate) && !empty($this->toDate)) {
            return $this->allOrderMob();            
        }
        
        return array("message"=>"Something went wrong");
    }

    public function get($id) {
        $orderModel = new DashboardOrder();
        $dashboardFunctions = new DashboardFunctions();
        $dashboardFunctions->getLocation();
        //$dashboardFunctions->getLocation();
        $currentDate = $dashboardFunctions->CityTimeZone();   

        /**
         * Get User Order Details Using Order Id
         */
        $order = $orderModel->getOrder(array(
            'columns' => array(
                'id',
                'user_id',
                'customer_first_name' => 'fname',
                'customer_last_name' => 'lname',
                'email',
                'order_date' => 'created_at',
                'status',
                'order_type1',
                'order_type2',
                'delivery_date' => 'delivery_time',
                'delivery_charge',
                'tax',
                'tip_amount',
                'order_type',
                'delivery_address',
                'deal_discount',
                'deal_title',
                'order_amount',
                'card_type',
                'card_number',
                'name_on_card',
                'expired_on',
                'special_instruction' => 'special_checks',
                'user_comments',
                'restaurant_id',
                'city',
                'state_code',
                'zipcode',
                'tip_percent',
                'promocode_discount',
                'redeem_point',
                'pay_via_point',
                'pay_via_card',
                'total_amount',
                'payment_receipt',
                'apt_suite',
                'phone',
                'billing_zip',
                'is_reviewed',
                'review_id',
                'cod',
                'created_at',
                'host_name',
                'order_pass_through',
                'city_id'
            ),
            'where' => array(
                'id' => $id
            ),
        ));

        if (!empty($order) && $order != null) {

            $OrderData = $order->getArrayCopy();
            $OrderData['cod'] = (int) $OrderData['cod'];
            $OrderData['pay_via_cash'] = ($OrderData['cod'] == 1) ? (string) number_format($OrderData['total_amount'] - $OrderData['pay_via_point'], 2) : "0";
            $joins = array();
            $restaurantModel = new \Dashboard\Model\Restaurant();
            $joins [] = array(
                'name' => array(
                    'c' => 'cities'
                ),
                'on' => 'restaurants.city_id = c.id',
                'columns' => array(
                    'city_name',
                    'state_code'
                ),
                'type' => 'INNER'
            );
            $restaurant = array(
                'columns' => array(
                    'city_id',
                    'order_pass_through',
                    'restaurant_name',
                    'restaurant_address' => 'address',
                    'zipcode',
                    'rest_code',
                    'restaurant_image_name'
                ),
                'where' => array(
                    'restaurants.id' => $OrderData['restaurant_id']
                ),
                'joins' => $joins
            );
            $restaurantModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
            $restaurantData = $restaurantModel->find($restaurant)->toArray();
            
            $config = $this->getServiceLocator()->get('Config');
            $orderStatus = isset($config['constants']['order_status']) ? $config['constants']['order_status'] : array();
            $astatus[] = $orderStatus[3];
            $astatus[] = $orderStatus[4];
            $astatus[] = $orderStatus[5];
            $astatus[] = $orderStatus[6];
            
            
            if(in_array($OrderData['status'],$astatus)){
                $OrderData['orderArchived'] = boolval(true);
            }  else {
                $OrderData['orderArchived'] = boolval(false);
            }
            $OrderData['current_date'] = $currentDate;
            $OrderData['restaurant_name'] = $restaurantData[0]['restaurant_name'];
            $OrderData['restaurant_address'] = $restaurantData[0]['restaurant_address'] . ", " . $restaurantData[0]['city_name'] . ", " . $restaurantData[0]['state_code'] . ", " . $restaurantData[0]['zipcode'];
            $OrderData['rest_code'] = strtolower($restaurantData[0]['rest_code']);
            $OrderData['time_of_delivery'] = \MCommons\StaticOptions::getFormattedDateTime($OrderData['delivery_date'], 'Y-m-d H:i:s', 'M d, Y @ H:i A');
            $OrderData['delivery_hour'] = \MCommons\StaticOptions::getFormattedDateTime($OrderData['delivery_date'], 'Y-m-d H:i:s', 'H');
            $OrderData['delivery_minutes'] = \MCommons\StaticOptions::getFormattedDateTime($OrderData['delivery_date'], 'Y-m-d H:i:s', 'i');
            $OrderData['time_of_order'] = \MCommons\StaticOptions::getFormattedDateTime($OrderData['order_date'], 'Y-m-d H:i:s', 'M d, Y @ H:i A');
            $OrderData['delivery_time_left'] = (int) strtotime($OrderData['delivery_date']) - strtotime($currentDate);
            $OrderData['delivery_time_left_hour'] = floor((strtotime($OrderData['delivery_date']) - strtotime($currentDate))/3600);
            $OrderData['created_time'] = date('h:i A', strtotime($OrderData['order_date']));
            $OrderData['special_checks'] = $OrderData['special_instruction'];
            $OrderData['restaurant_image_name'] = strtolower($restaurantData[0]['restaurant_image_name']);
            $OrderData['promocode_discount'] = number_format($OrderData['promocode_discount'], 2, '.', '');
            $OrderData['deal_discount'] = number_format($OrderData['deal_discount'], 2, '.', '');
            $OrderData['my_payment_details']['card_name'] = $OrderData['name_on_card'];
            $OrderData['my_payment_details']['card_number'] = $OrderData['card_number'];
            $OrderData['my_payment_details']['card_type'] = $OrderData['card_type'];
            if ($OrderData['expired_on']) {
                $expired_on = explode("/", $OrderData['expired_on']);
                $OrderData['my_payment_details']['expiry_year'] = $expired_on[1];
                $OrderData['my_payment_details']['expiry_month'] = $expired_on[0];
            } else {
                $expired_on[1] = '';
                $expired_on[0] = '';
                $OrderData['my_payment_details']['expiry_year'] = '';
                $OrderData['my_payment_details']['expiry_month'] = '';
            }
            $OrderData['my_payment_details']['billing_zip'] = $OrderData['billing_zip'];

            $OrderData['my_delivery_detail']['first_name'] = $OrderData['customer_first_name'];
            $OrderData['my_delivery_detail']['last_Name'] = $OrderData['customer_last_name'];
            $OrderData['my_delivery_detail']['email'] = $OrderData['email'];
            $OrderData['my_delivery_detail']['city'] = $OrderData['city'];
            $OrderData['my_delivery_detail']['apt_suite'] = $OrderData['apt_suite'];
            $OrderData['my_delivery_detail']['state'] = $OrderData['state_code'];
            $OrderData['my_delivery_detail']['phone'] = $OrderData['phone'];
            if (!empty($OrderData['delivery_address'])) {
                $address = $OrderData['delivery_address'] . ', ' . $OrderData['city'] . ', ' . $OrderData['state_code'] . ', ' . $OrderData['zipcode'];
            } else {
                $address = $OrderData['city'] . ', ' . $OrderData['state_code'] . ', ' . $OrderData['zipcode'];
            }
            $OrderData['my_delivery_detail']['address'] = $address;
            $OrderData['my_delivery_detail']['zipcode'] = $OrderData['zipcode'];

            $OrderData ['order_amount_calculation']['subtotal'] = number_format($OrderData['order_amount'], 2, '.', '');
            $OrderData ['order_amount_calculation']['tax_amount'] = $OrderData['tax'];
            $OrderData ['order_amount_calculation']['tip_amount'] = $OrderData['tip_amount'];
            $OrderData ['order_amount_calculation']['delivery_charge'] = $OrderData['delivery_charge'];
            $OrderData ['order_amount_calculation']['discount'] = ($OrderData['deal_discount'] > 0) ? $OrderData['deal_discount'] : "0";
            $OrderData ['order_amount_calculation']['promocode_discount'] = ($OrderData['promocode_discount'] > 0) ? $OrderData['promocode_discount'] : "0";
            $OrderData ['order_amount_calculation']['redeem_point'] = ($OrderData['redeem_point'] > 0) ? $OrderData['redeem_point'] : "0";
            $OrderData ['order_amount_calculation']['pay_via_point'] = ($OrderData['pay_via_point'] > 0) ? $OrderData['pay_via_point'] : "0";
            $OrderData ['order_amount_calculation']['pay_via_card'] = ($OrderData['pay_via_card'] > 0 && $OrderData['cod'] == 0) ? $OrderData['pay_via_card'] : "0";
            $OrderData ['order_amount_calculation']['pay_via_cash'] = ($OrderData['cod'] == 1) ? $OrderData['total_amount'] - $OrderData['pay_via_point'] : "0";
            $OrderData ['order_amount_calculation']['total_order_price'] = $OrderData['total_amount'];
            $OrderData['delivery_address'] = $OrderData['delivery_address'] . ", " . $OrderData['city'] . ", " . $OrderData['state_code'] . ", " . $OrderData['zipcode'];

            if (isset($OrderData) && !empty($OrderData)) {
                if ($OrderData['expired_on']) {
                    $months = explode('/', $OrderData['expired_on']);
                    $year = substr($months[1], - 2, 2);
                    $OrderData['expired_on'] = $months[0] . '/' . $year;
                } else {
                    $OrderData['expired_on'] = '';
                }

                $OrderData['card_type'] = strtoupper($OrderData['card_type']);
                $OrderData['order_type'] = ucfirst($OrderData['order_type']);
                $userOrderDetailModel = new \Dashboard\Model\OrderDetail();
                $joins_city = array();
                $joins_city [] = array(
                    'name' => array(
                        'm' => 'menus'
                    ),
                    'on' => 'user_order_details.item_id=m.id',
                    'columns' => array(
                        'item_status' => 'status'
                    ),
                    'type' => 'left'
                );
                $userOrderItem = $userOrderDetailModel->getAllOrderDetail(array(
                    'columns' => array(
                        'id',
                        'order_item_id' => 'item_id',
                        'item_name' => 'item',
                        'item_price_id',
                        'item_qty' => 'quantity',
                        'unit_price' => 'unit_price',
                        'item_special_instruction' => 'special_instruction',
                        'item_price_desc'
                    ),
                    'where' => array(
                        'user_order_id' => $id
                    ),
                    'joins' => $joins_city
                ));
                $userOrderItem[0]['item_status'] = intval($userOrderItem[0]['item_status']);
                $userOrderItem[0]['item_name'] = html_entity_decode($userOrderItem[0]['item_name']);
                $userOrderItem[0]['item_special_instruction'] = html_entity_decode($userOrderItem[0]['item_special_instruction']);
                $OrderData['item_list'] = $userOrderItem;
                $OrderData['card_type'] = strtoupper($OrderData['card_type']);
                $expiredOn = $OrderData['expired_on'];
                if (!empty($expiredOn)) {
                    $months = explode('/', $expiredOn);
                    $year = substr($months[1], - 2, 2);
                    $OrderData['expired_on'] = $months[0] . '/' . $year;
                }

                /**
                 * Get User Order Item Addons Using User Order Item Id
                 */
                $OrderAddonsModel = new \Dashboard\Model\OrderAddons();
                $i = 0;
                foreach ($OrderData['item_list'] as $key1 => $value) {

                    $orderItemId = $OrderData['item_list'][$i]['id'];

                    $addon = $OrderAddonsModel->getAllOrderAddon(array(
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
                            'user_order_detail_id' => $orderItemId
                        )
                    ));
                    $cc = array();
                    $j = 0;
                    if (!empty($addon)) {
                        foreach ($addon as $key => $result) {

                            if ($result['addons_option'] == 'None') {
                                continue;
                            } else {
                                $addons['addons_id'] = $result['menu_addons_id'];
                                $addons['addon_name'] = $dashboardFunctions->to_utf8($result['addons_option']);
                                $addons['addon_price'] = $result['price'];
                                $addons['addons_total_price'] = number_format($result['price'] * $result['quantity'], 2);
                                $addons['addon_quantity'] = $result['quantity'];
                                $cc[$j] = $addons;
                                $j++;
                            }
                        }
                    }
                    $OrderData['item_list'][$i]['item_name'] = $dashboardFunctions->to_utf8($OrderData['item_list'][$i]['item_name']);

                    if (!empty($cc)) {
                        $OrderData['item_list'][$i]['addons_list'] = $cc;
                        $cc = array();
                    } else {
                        $OrderData['item_list'][$i]['addons_list'] = array();
                    }
                    $i ++;
                }
                $orderSubTotal = 0;
                $orderSubTotal = (float) $OrderData['order_amount'];

                $orderTax = (float) $OrderData['tax'];
                if (is_numeric($orderTax) & $orderTax != 0) {
                    $OrderData['tax'] = number_format($orderTax, 2);
                    $orderSubTotal = $orderSubTotal + $orderTax;
                }
                $orderTip = (float) $OrderData['tip_amount'];
                if (is_numeric($orderTip) & $orderTip != 0) {
                    $OrderData['tip_amount'] = number_format($orderTip, 2);
                    $orderSubTotal = $orderSubTotal + $orderTip;
                }
                $orderDelCharge = (float) $OrderData['delivery_charge'];
                if (is_numeric($orderDelCharge) & $orderDelCharge != 0) {
                    $OrderData['delivery_charge'] = number_format($orderDelCharge, 2);
                    $orderSubTotal = $orderSubTotal + $orderDelCharge;
                }
                $orderDiscount = (float) $OrderData['deal_discount'];
                if (is_numeric($orderDiscount) & $orderDiscount != 0) {
                    $OrderData['deal_discount'] = number_format($orderDiscount, 2);
                    $orderSubTotal = $orderSubTotal - $orderDiscount;
                }
                $OrderData['order_amount'] = number_format($OrderData['order_amount'], 2, '.', ',');
                $OrderData['is_reviewed'] = ($OrderData['is_reviewed'] == "") ? intval(0) : intval($OrderData['is_reviewed']);
                $OrderData['review_id'] = ($OrderData['review_id'] == "") ? intval(0) : intval($OrderData['review_id']);
                $totalOrder = $orderModel->getTotalUserOrder($OrderData['user_id'], $OrderData['email'], $OrderData['restaurant_id']);

                //  $reservation = new \Dashboard\Model\DashboardReservation();
                // $totalReservation = $reservation->getTotalUserReservations($OrderData['user_id'], $OrderData['restaurant_id']);
                $userModel = new \Dashboard\Model\User();
                $options = array(
                    'columns' => array(
                        'display_pic_url_large'
                    ),
                    'where' => array(
                        'users.id' => $OrderData['user_id']
                    ),
                );
                $userPic = $userModel->getUserDetail($options);
                $userImage = $dashboardFunctions->findImageUrlNormal($userPic['display_pic_url_large'], $OrderData['user_id']);
                $OrderData['user_image'] = $userImage;
                $OrderData['user_default_image'] = ($userPic['display_pic_url_large'])?$userPic['display_pic_url_large']:"";
                $dashboardReservation = new \Dashboard\Model\DashboardReservation();
                $pastActivity = $dashboardReservation->getUserPastActivities($OrderData['user_id'], $OrderData['restaurant_id'], $OrderData['email']);
                $OrderData['user_activity'] = array('total_user_order' => (int) $pastActivity['totalorder'], 'total_user_review' => $pastActivity['totalreview'], 'total_user_checkin' => $pastActivity['totalcheckin'], 'total_user_reservation' => $pastActivity['totalreservation']);
                unset($OrderData['name_on_card'], $OrderData['card_number'], $OrderData['card_type'], $expired_on[1], $expired_on[0], $OrderData['billing_zip'], $OrderData['delivery_charge'], $OrderData['tax'], $OrderData['tip_amount'], $OrderData['delivery_address'], $OrderData['deal_discount'], $OrderData['deal_title'], $OrderData['order_amount'], $OrderData['expired_on'], $OrderData['city'], $OrderData['state_code'], $OrderData['zipcode'], $OrderData['apt_suite'], $OrderData['phone'], $OrderData['promocode_discount'], $OrderData['redeemed_point'], $OrderData['item_list'][0]['id'], $OrderData['item_list'][0]['item_price_id']);
                
                $currentVersion = $this->getQueryParams("current_version",false);
                $fourceUp=\MCommons\StaticOptions::fourceUpdate($currentVersion);
                $OrderData['fource_update'] = $fourceUp;
                return $OrderData;
            }
        } else {
            throw new \Exception('Order id is not valid', 400);
        }
    }

    public function update($order_id, $data) {  //update order status picked up to arraived and delever to sent
        $orderModel = new DashboardOrder();
        $dashboardFunctions = new DashboardFunctions();
        $restaurant = new \Dashboard\Model\Restaurant();
        $deliveryDate = (isset($data['delivery_date']) && !empty($data['delivery_date'])) ? $data['delivery_date'] : false;
        $reason = (isset($data['reason']) && !empty($data['reason'])) ? $data['reason'] : false;
        $this->customerCall = isset($data['customer_call']) ? $data['customer_call'] : false;
        if (!isset($data['status']) || empty($data['status'])) {
            throw new \Exception("Status not found", 400);
        }
        
        $status = $data['status']; //place=>confirmed, confirmed=>delivered/arrived,
        
        if($data['status']=="ready"){
            $status = "arrived";
        }
        
        if($data['status'] == "delivered" || $data['status'] == "arrived"){
            $status = "archived";
        }
         
        $dashboardFunctions->getLocation();
        $this->location = $dashboardFunctions->location;
        $this->currentDate = $dashboardFunctions->CityTimeZone();
        $this->restaurantId = $dashboardFunctions->restaurantId;
        $this->orderId = $order_id;        
        $options = array("columns" => array("rest_code","restaurant_name", "delivery", "takeout", "reservations", "facebook_url", "twitter_url", "instagram_url", "pinterest_url", "gmail_url","restaurant_logo_name"), "where" => array("id" => $this->restaurantId));
        $restaurantDetails = $restaurant->findRestaurant($options);

        if ($order_id) {
            $orderDetail = $this->get($order_id);
            $this->userId = $orderDetail['user_id'];
            //pr($orderDetail);
            $orderModel->id = $order_id;
            $orderModel->restaurant_id = $this->restaurantId;
            //$this->deliveryTime = $orderDetail['delivery_time'];
            $orderModel->updated_at = $this->currentDate;
            $orderModel->status = strtolower($status);
            $orderModel->restaurants_comments = $reason;           

            if ($deliveryDate) {
                $orderModel->delivery_time = $deliveryDate;
            }
            $this->hostName = $orderDetail['host_name'];
            $this->restaurantLogo = $restaurantDetails->restaurant_logo_name;
            $orderDetail['restaurant_name'] = $dashboardFunctions->to_utf8($orderDetail['restaurant_name']);
            $orderDetail['res_facebook_url'] = $restaurantDetails->facebook_url;
            $orderDetail['res_twitter_url'] = $restaurantDetails->twitter_url;
            $orderDetail['res_instagram_url'] = $restaurantDetails->instagram_url;
            $orderDetail['res_pinterest_url'] = $restaurantDetails->pinterest_url;
            $orderDetail['rest_code'] = $restaurantDetails->rest_code;
//            pr($orderDetail);
            if ($orderModel->updateOrderStatus()) { 
                if ($status == 'cancelled') {
                    $this->restaurantAddress = $restaurant->restaurantAddress($this->restaurantId);
                    $this->orderStatus = 'cancel';
                    $orderDetail['is_preorder'] = false;
                    if($reason){
                        $this->cancelReason = $reason;
                    }else if ($this->customerCall) {
                        $this->cancelReason = 'As we discussed earlier on the phone, we&#8217;ve canceled your order <strong>' . $orderDetail['payment_receipt'] . '</strong> per your request. We hope you&#8217;ll try ' . $orderDetail['restaurant_name'] . ' again soon!';
                    } else {
                        $this->cancelReason = 'We&#8217;re sorry we were unable to fulfill your order <strong>' . $orderDetail['payment_receipt'] . '</strong>, but we hope you&#8217;ll try us again soon.';
                    }
                    if($this->hostName==PROTOCOL.SITE_URL || $this->hostName=="iphone" || $this->hostName=="android"){
                        $mailData = $this->sendCancelOrderMail($orderDetail, $reason);
                    }else{                       
                        $mailData = $this->sendConfirmOrderMail($orderDetail);
                    }
                    //pr($mailData,1);
                    $dashboardFunctions->sendMails($mailData);
                    ############# SMS TO USER ##########
                    $this->sendSMSOrder($orderDetail,$status); 
                    ###############Notification#########
                    $this->userNotification($orderDetail, $status);
                }               
                
                ####################################
                $restaurantServer = new \Dashboard\Model\RestaurantServer();
                $restaurantServer->user_id = $this->userId;
                $restaurantServer->restaurant_id = $this->restaurantId;
                $this->isUserDineAndMore = ($restaurantServer->isUserRegisterWithRestaurant()[0]['count'] > 0)?true:false;
                
                $totalOrder0fUser = $orderModel->getTotalOrder($orderDetail['user_id'])['total_order'];
                $this->isFirstOrder = ($totalOrder0fUser == 1)?true:false;
                
                $dashboardFunctions->order_amount = $orderDetail['total_amount'];
                $dashboardFunctions->restaurant_name = $orderDetail['restaurant_name'];
                $dashboardFunctions->userId = $orderDetail['user_id'];
                $dashboardFunctions->isFirstOrder = $this->isFirstOrder;
                $dashboardFunctions->deliveryTime = $orderDetail['delivery_date']; 
                $dashboardFunctions->orderType = $orderDetail['order_type'];
                $dashboardFunctions->refId = $order_id;
                
                if($status == 'confirmed'){
                    $this->restaurantAddress = $restaurant->restaurantAddress($this->restaurantId);
                    $this->orderStatus = 'confirm';
                    if($this->isRegisterWithRestaurant()){
                        $this->orderPoint = $dashboardFunctions->awardPointForOrder();
                    }else{
                        $points['id'] = 1;
                        $points['points'] = (int)$orderDetail['total_amount'];
                        $points['message'] =  "You earned " . $points['points'] . " points with your " . $orderDetail['order_type'] . " order from " . $orderDetail['restaurant_name'] . "!";
                        $this->orderPoint = $points;
                    }
                    $dashboardFunctions->givePoints($this->orderPoint);    
                    
                    $orderDetail['is_preorder'] = $dashboardFunctions->isPreOrder($orderDetail['order_date'],$orderDetail['delivery_date']); 
                    $mailData = $this->sendConfirmOrderMail($orderDetail);
                    $dashboardFunctions->sendMails($mailData);
                    
                    ############# SMS TO USER ##########
                    $this->sendSMSOrder($orderDetail,$status); 
                    ###############Notification#########
                    $this->userNotification($orderDetail, $status);                   
                } 
                
                if($data['status']=="ready"){
                    $this->orderStatus = 'ready';
                    $this->restaurantAddress = $restaurant->restaurantAddress($this->restaurantId); 
                    $orderDetail['is_preorder'] = false;
                    $mailData = $this->sendConfirmOrderMail($orderDetail);
                    if($orderDetail['host_name']==PROTOCOL.SITE_URL || $orderDetail['host_name']=="iphone" || $orderDetail['host_name']=="android" ){
                        $mailData['template_name'] = READY_TACKOUT_ORDER;
                        $mailData['subject'] = sprintf(READY_TACKOUT_SUBJECT, $orderDetail['restaurant_name']);
                    }
                    
                    $dashboardFunctions->sendMails($mailData);
                    
                    ############# SMS TO USER ##########
                    //$this->sendSMSOrder($orderDetail,$status); 
                    ###############Notification#########
                    $this->userNotification($orderDetail, 'ready');
                    
                    $this->restaurantAddress = $restaurant->restaurantAddress($this->restaurantId);
                    //$this->salesManago($totalOrder0fUser, $restaurantDetails, $orderDetail);
                } 
                $totalliveOrder = $orderModel->liveOrderCount($this->restaurantId);
                return array("message" => true, "status" => $status, "order_id" => $order_id,'orders'=>(int)$totalliveOrder[0]['total_order']);
            }
            throw new \Exception('Order id is not valid', 400);
        } else {
            throw new \Exception('Order id is not valid', 400);
        }
    }

    public function cronOrder() {
        $cronOrder = new \Dashboard\Model\CronOrder();
        $cronOrder->order_id = $this->orderId;
        $cronOrder->delivery_time = $this->deliveryTime;
        $cronOrder->arrived_time = $this->currentDate;
        $cronOrder->archive_time = date('Y-m-d H:i:s', strtotime("+45 minutes", strtotime($this->currentDate)));
        $cronOrder->time_zone = $this->location['timezone'];
        $cronOrder->status = 1;
        $options = array("columns" => array("id"), "where" => array("order_id" => $this->orderId));
        $result = $cronOrder->getCronOrder($options);

        if ($result) {
            $cronOrder->id = $result->id;
        }
        $cronOrder->save();
    }

    public function salesManago($totalOrder0fUser, $restaurantDetails, $orderData) {
        $detail3 = ($restaurantDetails->delivery == 1 && $restaurantDetails->takeout == 1) ? 1 : 0;
        $detail4 = ($restaurantDetails->reservations == 1) ? 1 : 0;        

        $detail5 = "";
        $detail6 = "";
        $detail7 = "";

        if ($this->isUserDineAndMore && $totalOrder0fUser == 1) {
            $detail5 = "first ".$orderData['order_type'];
            $detail6 = "restaurant dine and more";
            $detail7 = "user dine and more";
        }

        $salesData = array(
            'detail5' => $detail5,
            'detail6' => $detail6,
            'detail7' => $detail7,
            'detail4' => $detail4,
            'detail3' => $detail3,
            'email' => $orderData['email'],
            'description' => strtolower($orderData['order_type']),
            'restaurant_name' => $restaurantDetails->restaurant_name,
            'location' => $this->restaurantAddress,
            'value' => $orderData['total_amount'],
            'contact_ext_event_type' => 'PURCHASE',
            'detail1' => $this->restaurantId,
            'externalId' => $this->orderId,
            'restaurant_id' => $this->restaurantId
        );

        $salesManago = new \Salesmanago();
        //$salesManago->eventsOnSalesmanago($salesData);
        return true;
    }

    public function sendConfirmOrderMail($order_detail, $orderTotalPointEarn = 0) {
        $data = [];
        if (!empty($order_detail)) {
            $special_checks = '';
            if (!empty($order_detail['special_instruction'])) {
                $special_checks = explode('||', $order_detail['special_instruction']);
                if (count($special_checks) > 0) {
                    $special_checks = implode(', ', $special_checks);
                }
            }

            if (!empty($order_detail['user_comments'])) {
                $special_checks = $special_checks . '<br />' . $order_detail['user_comments'];
            }
            
            $delivery_to = "";
            if($order_detail['order_type'] == "Delivery"){
                $delivery_to = $order_detail['my_delivery_detail']['address'];

                if (!empty($order_detail['my_delivery_detail']['apt_suite'])) {
                    $delivery_to = $order_detail['my_delivery_detail']['apt_suite'] . ', ' . $delivery_to;
                }
            }

            $card_number = "";

            if ($order_detail['my_payment_details']['card_number']) {               
                $card_number = '<tr bgcolor="#f5f4f4">
                                <td style="padding:20px;font-size:18px;font-family:arial;"><strong>Payment:</strong><br /><br /><strong>Credit Card:</strong><br/>' . $order_detail['my_payment_details']['card_type'] . '(XXXX-XXXX-XXXX-' . $order_detail['my_payment_details']['card_number'] . '/<br>Exp:' . $order_detail['my_payment_details']['expiry_month'] . "/" . $order_detail['my_payment_details']['expiry_year'] . ')</td>
                            </tr>';
            }
            $order_type = 'Individual ' . $order_detail['order_type'];
            $host_name = $order_detail['host_name'];

            $res_address = $order_detail['restaurant_address'];
           
            $order_string = $this->getOrderDetailItems($order_detail['item_list']);
            
            $time_of_order = date("D, M d, Y", strtotime($order_detail['order_date'])) . " at " . date("h:i A", strtotime($order_detail['order_date']));
            $time_to_delivery = date("D, M d, Y", strtotime($order_detail['delivery_date'])) . " at " . date("h:i A", strtotime($order_detail['delivery_date']));
            

            $delivery_charge_txt = "";
            if ($order_detail['order_amount_calculation']['delivery_charge'] > 0) {
                $delivery_charge_txt = '<tr><td align="right">Delivery Charge:</td><td align="left">$' . $order_detail['order_amount_calculation']['delivery_charge'] . '</td></tr>';
            }

            $promocode_discount_txt = "";
            $discount_txt = "";
            $redeemed_point_text = "";
            if ($order_detail['order_amount_calculation']['discount'] > 0) {
                $discount_txt = '<tr><td align="right">Deal Discount:</td><td align="left">$' . $order_detail['order_amount_calculation']['discount'] . '</td></tr>';
            }
            if ($order_detail['order_amount_calculation']['promocode_discount'] > 0) {
                $promocode_discount_txt = '<tr><td align="right">Promocode Discount:</td><td align="left">$' . $order_detail['order_amount_calculation']['promocode_discount'] . '</td></tr>';
            }
            if ($order_detail['redeem_point'] > 0) {
                $redeemed_point_text.= '<tr><td align="right">Pay By Card:</td><td align="left">$' . $order_detail['order_amount_calculation']['pay_via_card'] . '</td></tr>';
                $redeemed_point_text.= '<tr><td align="right">Pay By Point(' . $order_detail['redeem_point'] . '):</td><td align="left">$' . $order_detail['order_amount_calculation']['pay_via_point'] . '</td></tr>';
            }
            $sl = \MCommons\StaticOptions::getServiceLocator();
            $config = $sl->get('Config');
            $webUrl = PROTOCOL . $config['constants']['web_url'];
            $order_url = $webUrl . 'order';
            $reserve_url = $webUrl . 'reserve';
            $deliveryestimate = "Time to Takeout";
            if ($order_detail['order_type'] == 'Delivery') {
                $deliveryestimate = "Time to Delivery";
            }
           if($host_name==PROTOCOL.SITE_URL || $host_name=="iphone" || $host_name=="android" ){
               $host_name = PROTOCOL.SITE_URL;
                $layout = "email-layout/default_new";
                if ($order_detail['order_type'] == 'Delivery') {
                    $template = ORDER_SUBMIT_INDIVIDUAL_2;
                    $subject = sprintf(SUBJECT_ORDER_SUBMIT_INDIVIDUAL_2, $order_detail['restaurant_name']);
                    

                    if ($order_detail['is_preorder']) {
                        $template = PRE_ORDER_CONFIRM;
                        $subject = sprintf(SUBJECT_PRE_ORDER_CONFIRM,$order_detail['restaurant_name']);
                    }
                } else {
                    $template = CONFIRM_TACKOUT_ORDER;
                    $subject = sprintf(SUBJECT_ORDER_SUBMIT_INDIVIDUAL_2, $order_detail['restaurant_name']);
                     if ($order_detail['is_preorder']) {
                        $template = PRE_ORDER_CONFIRM;
                        $subject = sprintf(SUBJECT_PRE_ORDER_CONFIRM,$order_detail['restaurant_name']);
                    }
                   
                }
            }else{
                $layout = "email-layout/ma_default";
                $template = "ma_micro_order_confirm";
                if($this->orderStatus=="confirm"){
                    $subject = sprintf("Order Up at %s!", $order_detail['restaurant_name']);
                }elseif($this->orderStatus=="cancel"){
                    $subject = sprintf("%s Order Cancellation", $order_detail['restaurant_name']);
                }elseif($this->orderStatus=="ready" && $order_detail['order_type']=="Takeout"){
                    $subject = sprintf("You're Takeout Order Is Ready!", $order_detail['restaurant_name']);
                }
            }

            if (!empty($order_string)) {
                $variables = array('host_name' => $host_name,
                    'name' => ucfirst($order_detail['customer_first_name']),
                    'site_url' => $webUrl,
                    'restaurant_name' => $order_detail['restaurant_name'],
                    'order_data' => $order_string,
                    'order_type' => $order_type,
                    'sub_total' => $order_detail['order_amount_calculation']['subtotal'],
                    'tax' => $order_detail['order_amount_calculation']['tax_amount'],
                    'tip_amount' => $order_detail['order_amount_calculation']['tip_amount'],
                    'delivery_charge' => $delivery_charge_txt,
                    'deal_discount' => $order_detail['order_amount_calculation']['discount'],
                    'promo_discount' => $promocode_discount_txt,
                    'total' => $order_detail['total_amount'],
                    'delivery_to' => $delivery_to,
                    'time_of_order' => $time_of_order, //date("D, M d, Y") . " at " . date("h:i A"),
                    'create_order' => date("D d, F") . " at " . date("h:i A"),
                    'reply_to' => EMAIL_FROM,
                    'time_to_delivery' => $time_to_delivery,
                    'order_url' => $order_url,
                    'reserve_url' => $reserve_url,
                    'card_number' => $card_number, //$order_detail['my_payment_details']['card_number'],
                    'card_type' => $order_detail['my_payment_details']['card_type'],
                    'expired_on' => $order_detail['my_payment_details']['expiry_month'] . "/" . $order_detail['my_payment_details']['expiry_year'],
                    'receipt_number' => $order_detail['payment_receipt'],
                    'delivery_instruction' => $special_checks,
                    'loyalty_points' => $orderTotalPointEarn,
                    'phone_no' => $order_detail['my_delivery_detail']['phone'],
                    'facebook_url' => $order_detail['res_facebook_url'],
                    'instagram_url' => $order_detail['res_instagram_url'],
                    'twitter_url'=> $order_detail['res_twitter_url'],
                    'address' => $res_address,
                    'redeemed_point_text' => $redeemed_point_text,
                    'deliveryestimate' => $deliveryestimate,
                    'loyality_points'=>$this->orderPoint['points'],
                    'addressMapIt'=>$order_detail['restaurant_name']." ".$this->restaurantAddress,
                    'restaurant_address'=>$this->restaurantAddress,
                    'restaurant_logo'=>$this->restaurantLogo,
                    'is_ordertype'=>  strtolower($order_detail['order_type']),
                    'status'=>$this->orderStatus,
                    'cancelReason'=>$this->cancelReason,
                    'rest_code'=>  strtolower($order_detail['rest_code']),
                    'is_preorder'=>$order_detail['is_preorder']
                        );
            
                $data = array(
                    'to' => array($order_detail['email']),
                    'from' => DASHBOARD_EMAIL_FROM,
                    'template_name' => $template,
                    'layout' => $layout,
                    'subject' => $subject,
                    'variables' => $variables
                );

//                    $deliveryTime = date("Y-m-d H:i:s", strtotime($orderDeliveryTime));
//                    $sms_delivery_times = date("F jS, Y \a\\t h:i A", strtotime($orderDeliveryTime));
//                    $sms_times = date("g:i A", strtotime($orderDeliveryTime));
            }
        }
        return $data;
    }

    public function getOrderDetailItems($items_data) {
        $order_string = '';

        $order_string .='<td bgcolor="#fff0e1" style="padding:10px 30px;">
                             <p style="margin:0;font-family:arial;font-size:16px;font-weight:bold;padding-bottom:9px;">Your Order:</p>
                             <table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-collapse:collapse;font-family:arial;font-size:16px;">
                                <tr>
                                   <td width="40%" align="left" valign="top" style="font-family:arial;">Item</td>
                                   <td align="center" valign="top style="font-family:arial;"">Unit Price</td>
                                   <td align="center" valign="top" style="font-family:arial;">Quantity</td>
                                   <td align="right" valign="top" style="font-family:arial;">Total</td>
                                </tr>';
        $i = 1;

        foreach ($items_data as $key => $item) {
            $order_string .='<tr style="font-size:14px;color:#686868;">
                    <td align="left" valign="top" style="padding-top:10px;font-family:arial;">' . utf8_encode($item['item_name']) . '</td>
                    <td align="center" valign="top" style="padding-top:10px;font-family:arial;">$' . number_format($item['unit_price'], 2) . '</td>  
                    <td align="center" valign="top" style="padding-top:10px;font-family:arial;">' . $item['item_qty'] . '</td>
                    <td align="right" valign="top" style="padding-top:10px;font-family:arial;">$' . number_format($item['unit_price'] * $item['item_qty'], 2) . '</td>
                  </tr>';

            if (!empty($item['addons_list'])) {
                foreach ($item['addons_list'] as $addkey => $addon) {
                    $order_string .='<tr style="font-size:11px;color:#686868;">
                             <td align="left" valign="top" style="padding-top:5px;font-family:arial;">' . utf8_encode($addon['addon_name']) . '</td>
                             <td align="center" valign="top" style="padding-top:5px;font-family:arial;">$' . number_format($addon['addon_price'], 2) . '</td>
                             <td align="center" valign="top" style="padding-top:5px;font-family:arial;">' . $addon['addon_quantity'] . '</td>
                             <td align="right" valign="top" style="padding-top:5px;font-family:arial;">$' . number_format($addon['addon_price'] * $addon['addon_quantity'], 2) . '</td>
                          </tr>';
                }
            }
            if (!empty($item->special_instruction)) {
                $order_string .='<tr style="font-size:10px;color:#686868;font-style: italic;">
                    <td align="left" valign="top">' . $item['item_special_instruction'] . '</td>
                                         <td></td>
                                         <td></td>
                                         <td></td>
                                      </tr>';
            }
            $i++;
        }
        $order_string .='</table></td>';
        return $order_string;
    }

    public function sendCancelOrderMail($order_detail, $reason) {
        $data = [];
        if (!empty($order_detail)) {
            $delivery_time = $order_detail['delivery_date'];
            $time_to_delivery = '<strong>' . date("D, M d, Y", strtotime($delivery_time)) . "</strong> at <strong>" . date("h:i A", strtotime($delivery_time)) . '</strong>';
            $host_name = $order_detail['host_name'];
            $htmlRestaurant_comments = '';
            if ($this->customerCall) {
                $msg = 'As we discussed earlier on the phone, we&#8217;ve canceled your order <strong>' . $order_detail['payment_receipt'] . '</strong> per your request. We hope you&#8217;ll try ' . $order_detail['restaurant_name'] . ' again soon!';
            } else {
                $msg = 'We&#8217;re sorry we were unable to fulfill your order <strong>' . $order_detail['payment_receipt'] . '</strong>, but we hope you&#8217;ll try us again soon.';
            }
            
            if($reason){
                $msg = $reason;
            }


            $template = '';
            $subject = '';
            $res_address = '';

            if ($host_name == 'aria') {
                $template = ORDER_CANCEL_ARIA;
                $subject = $order_detail['restaurant_name'] . ' Order Cancellation';
                $layout = 'email-layout/default_aria';
                if (strtoupper($order_detail['rest_code']) === ARIAHK_REST_CODE) {
                    $res_address = '<tr><td align="center" style="font-size:0"><img src="' . TEMPLATE_IMG_PATH . 'add_hellkitchen.jpg" alt="Aria West Village 117 Perry St, New York, NY 10014 b/t Hudson St & Greenwich St" style="border: none; width: 100%; max-width: 100%; height: auto;display:block;"></td></tr><tr><td align="center" style="font-size:0"><img src="' . TEMPLATE_IMG_PATH . 'add_westvillage.jpg" alt="Aria hell\'s kitchen 369 W 51st St, New York, NY 10019 b/t 9th Ave & 8th Ave" style="border: none; width: 100%; max-width: 100%; height: auto;display:block;"></td></tr>';
                } else {
                    $res_address = '<tr><td align="center" style="font-size:0"><img src="' . TEMPLATE_IMG_PATH . 'add_westvillage.jpg" alt="Aria hell\'s kitchen 369 W 51st St, New York, NY 10019 b/t 9th Ave & 8th Ave" style="border: none; width: 100%; max-width: 100%; height: auto;display:block;"></td></tr><tr><td align="center" style="font-size:0"><img src="' . TEMPLATE_IMG_PATH . 'add_hellkitchen.jpg" alt="Aria West Village 117 Perry St, New York, NY 10014 b/t Hudson St & Greenwich St" style="border: none; width: 100%; max-width: 100%; height: auto;display:block;"></td></tr>';
                }
            } else {
                $template = ORDER_CANCEL_INDIVIDUAL;
                $subject = SUBJECT_ORDER_CANCEL_INDIVIDUAL;
                $layout = 'email-layout/default_new';
            }

            $variables = array(
                'name' => ucfirst($order_detail['customer_first_name']),
                'restaurant_name' => $order_detail['restaurant_name'],
                'reply_to' => DASHBOARD_EMAIL_FROM,
                'time_to_delivery' => $time_to_delivery,
                'address' => $res_address,
                'content' => $msg,
                'facebook_url' => $order_detail['res_facebook_url'],
                'instagram_url' => $order_detail['res_instagram_url'],                
            );

            $data = array(
                'to' => array($order_detail['email']),
                'from' => DASHBOARD_EMAIL_FROM,
                'template_name' => $template,
                'layout' => $layout,
                'subject' => $subject,
                'variables' => $variables
            );
        }
        return $data;
    }

    public function formatPhone($num) {
        $num = preg_replace('/[^0-9]/', '', $num);

        $len = strlen($num);
        if ($len == 7)
            $num = preg_replace('/([0-9]{3})([0-9]{4})/', '$1-$2', $num);
        elseif ($len == 10)
            $num = preg_replace('/([0-9]{3})([0-9]{3})([0-9]{4})/', '$1-$2-$3', $num);

        return $num;
    }

    public function sendSMSOrder($orderDetail, $status) {
        //if ($orderDetail['host_name'] === "munchado.com" || $orderDetail['host_name'] === "munchado") { //send SmS 
        //$currentOrderTimeSlotStatus = UserOrder::getcurrentOrderTimeSlotStatus($created_at, $delivery_time);
        if ($orderDetail['order_type'] == 'Delivery') {
            if ($status == "confirmed") {
                $sms_msg = $orderDetail['restaurant_name'] . " has officially confirmed your order. Lucky you! Remember, you may be asked to sign a receipt at the time of delivery. You do not need to re-enter the tip.";
            } else if ($status == "cancelled") {
                $sms_msg = $orderDetail['restaurant_name'] . " did not accept you order. :( Check your inbox for more details. Don't let it keep you down, place another order!";
            }
        } else { //Order Status - Takeout
            if ($status == "confirmed") {
                $sms_msg = $orderDetail['restaurant_name'] . " has officially confirmed your order. Lucky you!";
            } else if ($status == "cancelled") {
                //if($currentOrderTimeSlotStatus){
                $sms_msg = $orderDetail['restaurant_name'] . " did not accept you order. :( Check your inbox for more details. Don't let it keep you down, place another order!";
//                   }else{
//                      $sms_msg = $restname." did not accept your pre-order. Don't let it keep you down though, place another order!"; 
//                   }                    
            }
        }
        $sms_message = array(
            "user_mob_no" => $orderDetail['my_delivery_detail']['phone'],
            "message" => $sms_msg
        );
        //pr($sms_message,1);
        \MCommons\StaticOptions::sendSmsClickaTell($sms_message, $orderDetail['user_id']);
        //}
    }

    public function userNotification($orderDetail, $status) {
        $msg = '';
        switch ($status) {
            case 'place':
                $msg = "We've successfully changed your pre-order for you. Bummer.";
               break;
            case 'cancelled':
//                if ($orderDetail['order_type'] === "Delivery") {
//                    $msg = "We've successfully canceled your order for you. Bummer.";
//                } elseif ($orderDetail['order_type'] === "Takeout") {
//                    $msg = "Your order was successfully cancelled. Bummer.";
//                } elseif ($orderDetail['status'] === "placed") {
                    $msg = "We've successfully canceled your pre-order for you. Bummer.";
//                }
                break;
            case 'confirmed':
                if ($orderDetail['order_type'] === "Delivery") {
                    $msg = ucfirst($orderDetail['restaurant_name']) . " has accepted, and started preparing your order. Get hungry.";
                } elseif ($orderDetail['order_type'] === "Takeout") {
                    $msg = ucfirst($orderDetail['restaurant_name']) . " has accepted, and started preparing your order.";
                }
                break;
            case 'rejected':
                if ($orderDetail['status'] === "ordered") {
                    $msg = ucfirst($orderDetail['restaurant_name']) . " had to cancel your order. Don&#8217;t let it keep you down though, place another order!";
                } else if ($orderDetail['status'] === "placed") {
                    $msg = ucfirst($orderDetail['restaurant_name']) . " had to cancel your pre-order. Don&#8217;t let it keep you down though, place another order!";
                }
                break;
            case 'delivered':
                $msg = $orderDetail['restaurant_name'] . " told us they delivered your order. It was to you...right?";
                break;
            case 'ready':
                $msg = $orderDetail['restaurant_name'] . " finished preparing your takeout order and is waiting patiently for you to claim it.";
                break;

//            case 'delivered':
//                $msg = $orderDetail['restaurant_name'] . " told us they delivered your order. It was to you...right?";
//                break;
//            case 'ready':
//                $msg = $orderDetail['restaurant_name'] . " finished preparing your takeout order and is waiting patiently for you to claim it.";
//
//                /* send to dashboard */
//                if ($orderDetail['order_type'] === "Takeout") {
//                    $msg = 'You\'ve marked takeout order number:' . $orderDetail['payment_receipt'] . ' as "Ready."';
//                }
//                $notificationJsonArray = array('is_friend' => 0, 'username' => ucfirst($orderDetail['customer_first_name']), 'order_id' => $orderDetail['id'], 'user_id' => $orderDetail['user_id'], 'order_status' => $orderDetail['status']);
//
//                $dashboardArray = array(
//                    "msg" => $msg,
//                    "channel" => "dashboard_" . $orderDetail['restaurant_id'],
//                    "userId" => $reservationData ['user_id'],
//                    "type" => 'order',
//                    "restaurantId" => $orderDetail ['restaurant_id'],
//                    'curDate' => $this->currentDate,
//                    'is_friend' => 0,
//                    'username' => ucfirst($orderDetail ['customer_first_name']),
//                    'order_id' => $orderDetail['id'],
//                    'user_id' => $orderDetail ['user_id'],
//                    'order_status' => 0
//                );
//                $dashboardNotificationModel = new \Dashboard\Model\UserDashboardNotification ();
//                $dashboardNotificationModel->createPubNubDashboardNotification($dashboardArray, $notificationJsonArray);
//                \MCommons\StaticOptions::pubnubPushNotification($dashboardArray);
//                break;
        }
//      pr($pubnubInfo);
//      pr($notificationArray,1);
        $pubnubInfo = array("user_id" => $orderDetail['user_id'], "restaurant_id" => $orderDetail['restaurant_id'], "restaurant_name" => $orderDetail['restaurant_name'], "order_id" => $orderDetail['id'], 'order_status' => $status);
        $notificationArray = array(
            "msg" => $msg,
            "channel" => "mymunchado_" . $orderDetail['user_id'],
            "userId" => $orderDetail['user_id'],
            "type" => 'order',
            "restaurantId" => $orderDetail['restaurant_id'],
            'curDate' => $this->currentDate,
        );
        $userNotificationModel = new \Dashboard\Model\UserNotification();
        $userNotificationModel->createPubNubNotification($notificationArray, $pubnubInfo);
        \MCommons\StaticOptions::pubnubPushNotification($notificationArray);

        return 'success';
    }
    
public function isRegisterWithRestaurant($userid=false) {
        $resServer = new \Dashboard\Model\RestaurantServer();
        $resServer->user_id = ($this->userId)?$this->userId:$userid;
        $resServer->restaurant_id = $this->restaurantId;
        $resServerData = $resServer->isUserRegisterWithRestaurant();
        if ($resServerData['0']['count'] > 0) {
            return false;
        }

        return true;
    }
    
    public function liveOrderMob() {
        $orderModel = new DashboardOrder();
        $sl = $this->getServiceLocator();
        $totalLiveRecords = 0;
        $config = $sl->get('Config');
        $orderStatus = isset($config['constants']['order_status']) ? $config['constants']['order_status'] : array();
        $status[] = $orderStatus[0];
        $status[] = $orderStatus[1];
        $status[] = $orderStatus[2];
        $status[] = $orderStatus[3];
        $status[] = $orderStatus[6];
        
        

        $options = array(
            'restaurant_id' => $this->restaurantId,
            'offset' => $this->offset,
            'orderby' => $this->orderby,
            'orderStatus' => $status,
            'currentDate' => $this->currentDate,
            'limit' => $this->limit,
            'type' => $this->type
        );
        $liveOrder = $orderModel->getLiveOrderForMob($options);
        if ($liveOrder) {
            $response['live_order'] = $liveOrder;
            $totalLiveRecords = count($liveOrder);
        } else {
            $response['live_order'] = array();
        }
        $response['total_live_records'] = $totalLiveRecords;
        $currentVersion = $this->getQueryParams("current_version",false);
        $fourceUp=\MCommons\StaticOptions::fourceUpdate($currentVersion);
        $response['fource_update'] = $fourceUp;
        return $response;
    }
    
    public function archiveOrderMob(){
       $orderModel = new DashboardOrder();
        $sl = $this->getServiceLocator();
        $totalArchiveRecords = 0;     
        $config = $sl->get('Config');
        $orderStatus = isset($config['constants']['order_status']) ? $config['constants']['order_status'] : array();
        $astatus[] = "archived";        
        $optionsArchive = array(
            'restaurant_id' => $this->restaurantId,
            'offset' => $this->offset,
            'orderby' => $this->orderby,
            'currentDate' => $this->currentDate,
            'limit' => $this->limit,
            'orderStatus' => $astatus,
            'type' => 'archive'
        );

        $archiveOrder = $orderModel->getArchiveOrderForMob($optionsArchive);
        $totalArchiveRecords = $archiveOrder['archive_count'];
        unset($archiveOrder['archive_count']);

        if ($archiveOrder) {
            $response['archive_order'] = $archiveOrder;
        } else {
            $response['archive_order'] = array();
        }
        $response['total_archive_records'] = $totalArchiveRecords;
        $currentVersion = $this->getQueryParams("current_version",false);
        $fourceUp=\MCommons\StaticOptions::fourceUpdate($currentVersion);
        $response['fource_update'] = $fourceUp;
        return $response;
    }
    
     public function liveOrderWs() {
        $orderModel = new DashboardOrder();
        $sl = $this->getServiceLocator();
        $totalLiveRecords = 0;
        $config = $sl->get('Config');
        $orderStatus = isset($config['constants']['order_status']) ? $config['constants']['order_status'] : array();
        $status[] = $orderStatus[2];        

        $options = array(
            'restaurant_id' => $this->restaurantId,
            'offset' => $this->offset,
            'orderby' => $this->orderby,
            'orderStatus' => $status,
            'currentDate' => $this->currentDate,
            'limit' => $this->limit,
            'type' => $this->type
        );
        $liveOrder = $orderModel->getLiveOrder($options);
        if ($liveOrder) {
            $response['live_order'] = $liveOrder;
            $totalLiveRecords = count($liveOrder);
        } else {
            $response['live_order'] = array();
            $totalLiveRecords = 0;
        }
        $response['total_live_records'] = $totalLiveRecords;
        return $response;
    }
     public function archiveOrderWs(){
       $orderModel = new DashboardOrder();
        $sl = $this->getServiceLocator();
        $totalArchiveRecords = 0;     
        $config = $sl->get('Config');
        $orderStatus = isset($config['constants']['order_status']) ? $config['constants']['order_status'] : array();
        $astatus[] = $orderStatus[3];
        $astatus[] = $orderStatus[6];
        $astatus[] = $orderStatus[4];
        $astatus[] = $orderStatus[5];
        $optionsArchive = array(
            'restaurant_id' => $this->restaurantId,
            'offset' => $this->offset,
            'orderby' => $this->orderby,
            'currentDate' => $this->currentDate,
            'limit' => $this->limit,
            'orderStatus' => $astatus,
            'type' => 'archive'
        );

        $archiveOrder = $orderModel->getArchiveOrder($optionsArchive);
        $totalArchiveRecords = $archiveOrder['archive_count'];
        unset($archiveOrder['archive_count']);

        if ($archiveOrder) {
            $response['archive_order'] = $archiveOrder;
        } else {
            $response['archive_order'] = array();
        }
        $response['total_archive_records'] = $totalArchiveRecords;
        return $response;
    }
    public function allOrderMob() {
        $orderModel = new DashboardOrder();
        $sl = $this->getServiceLocator();
        $totalLiveRecords = 0;
        $config = $sl->get('Config');
        $orderStatus = isset($config['constants']['order_status']) ? $config['constants']['order_status'] : array();
        $status[] = $orderStatus[0];
        $status[] = $orderStatus[2];
        $status[] = $orderStatus[6];
        $status[] = $orderStatus[3];
        //$status[] = $orderStatus[8];

        $options = array(
            'restaurant_id' => $this->restaurantId,
            'offset' => $this->offset,
            'orderby' => $this->orderby,
            //'orderStatus' => $status,
            'currentDate' => $this->currentDate,
            'limit' => $this->limit,
            'type' => $this->type,
            'fromDate' => $this->fromDate. " 00:00:00",
            'toDate' => $this->toDate. " 23:59:59"
        );
        $orders = $orderModel->getAllOrdersForMob($options);
        if ($orders) {
            $response['orders'] = $orders;
            $totalRecords = count($orders);
        } else {
            $totalRecords['orders'] = array();
        }
        $response['total_records'] = $totalRecords;
        $currentVersion = $this->getQueryParams("current_version",false);
        $fourceUp=\MCommons\StaticOptions::fourceUpdate($currentVersion);
        $response['fource_update'] = $fourceUp;
        return $response;
    }
}
