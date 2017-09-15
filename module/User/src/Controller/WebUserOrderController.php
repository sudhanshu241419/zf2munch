<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\UserFunctions;
use User\Model\UserOrder;
use Restaurant\Model\PreOrder;
use MCommons\StaticOptions;
use Restaurant\Model\PreOrderAddons;
use User\Model\UserOrderDetail;
use User\Model\UserOrderAddons;
use User\Model\UserNotification;
use Restaurant\Model\Restaurant;
use User\Model\User;
use Restaurant\Model\City;

class WebUserOrderController extends AbstractRestfulController {

    public function getList() {
        $userFunctions = new UserFunctions();
        $userOrderModel = new UserOrder();
        $preOrderModel = new PreOrder();
        $session = $this->getUserSession();
        $isLoggedIn = $session->isLoggedIn();
        $restaurantId = $this->getQueryParams("restaurantid",false);
        if ($isLoggedIn) {
            $userId = $session->getUserId();
        } else {
            throw new \Exception('User detail not found', 404);
        }
        $locationData = $session->getUserDetail('selected_location');
        $currentDate = $userFunctions->userCityTimeZone($locationData);
        $orderby = $this->getQueryParams('orderby', 'date');
        $page = $this->getQueryParams('page', 1);
        $limit = $this->getQueryParams('limit', 50);
        $type = $this->getQueryParams('type');
        $offset = 0;
        $response = array();
        if ($page > 0) {
            $page = ($page < 1) ? 1 : $page;
            $offset = ($page - 1) * (SHOW_PER_PAGE);
        }
        $sl = $this->getServiceLocator();
        $config = $sl->get('Config');
        $orderStatus = isset($config['constants']['order_status']) ? $config['constants']['order_status'] : array();
        /**
         * Get User Live Orders
         */
        if ($type == 'live') {
            $status[] = $orderStatus[0];
            $status[] = $orderStatus[1];
            $status[] = $orderStatus[2];
            $status[] = $orderStatus[3];
            //$status[] = $orderStatus[5];
            $status[] = $orderStatus[6];
            $status[] = $orderStatus[8];

            $options = array(
                'userId' => $userId,
                'orderStatus' => $status,
                'currentDate' => $currentDate,
                'limit' => $limit,
                'restaurantId'=>$restaurantId
            );
            $response = $userOrderModel->getUserLiveOrder($options);

            /**
             * Get User Pre Orders
             */
            /*
             * $orderType = isset($config['constants']['order_type']) ? $config['constants']['order_type'] : array(); $type = $orderType['orderPending']; $options = array( 'userId' => $userId, 'offset' => $offset, 'orderby' => $orderby, 'orderStatus' => $type, 'currentDate' => $currentDate, 'limit' => $limit );
             */
            /* $archiveOrder = $preOrderModel->getUserAllPreOrder($options);
              if (empty($response)) {
              return $archiveOrder;
              } elseif (empty($archiveOrder)) {
              return $response;
              } elseif (! empty($response) && ! empty($archiveOrder)) {
              $data = array_merge($response, $archiveOrder);
              return $data;
              } else {
              return array();
              } */
            if (!empty($response)) {
                return $response;
            } else {
                return array();
            }
        } /**
         * Get User Live Archive Orders
         */ elseif ($type == 'archive') {
            $options = array(
                'userId' => $userId,
                'offset' => $offset,
                'orderby' => $orderby,
                // 'orderStatus' => $orderStatus,
                'currentDate' => $currentDate,
                'limit' => $limit,
                'restaurantId'=>$restaurantId
            );
            $archiveOrder = $userOrderModel->getUserArchiveOrder($options);            
            return $archiveOrder;
        } elseif ($type == 'count') {

            /*
             * $status = $orderStatus[4]; $orderType = isset($config['constants']['order_type']) ? $config['constants']['order_type'] : array(); $type = $orderType['orderPending']; $orderCount = $userOrderModel->getTotalOrder($userId, $status); //$preOrderCount = $preOrderModel->getUserPreOrderCount($userId, $type, $currentDate); //$total = $orderCount['total_order'] + $preOrderCount['total_order']; $total = $orderCount['total_order'] ;
             */
            $status[] = $orderStatus[0];
            $status[] = $orderStatus[1];
            $status[] = $orderStatus[2];
            $status[] = $orderStatus[3];
            //$status[] = $orderStatus[4];
            $status[] = $orderStatus[6];
            //$status[] = $orderStatus[8];

            $options = array(
                'userId' => $userId,
                'orderStatus' => $status,
                'currentDate' => $currentDate,
                'limit' => $limit
            );
            $response = $userOrderModel->getUserLiveOrder($options);

            $options = array(
                'userId' => $userId,
                'offset' => $offset,
                'orderby' => $orderby,
                'orderStatus' => $orderStatus,
                'currentDate' => $currentDate,
                'limit' => $limit,
                'flag' => 'count',
                'restaurantId'=>$restaurantId
            );
            $archiveOrder = $userOrderModel->getUserArchiveOrder($options);

            $archiveTotal = count($archiveOrder);
            $liveTotal = count($response);
            $total = $archiveTotal + $liveTotal;
            return array(
                'total_order' => isset($total) ? $total : 0
            );
        } elseif ($type == 'archive_count') {
            $options = array(
                'userId' => $userId,
                'offset' => $offset,
                'orderby' => $orderby,
                'orderStatus' => $orderStatus,
                'currentDate' => $currentDate,
                'limit' => $limit,
                'flag' => 'count',
                'restaurantId'=>$restaurantId
            );
            $archiveOrder = $userOrderModel->getUserArchiveOrder($options);
            // $total = $archiveOrderCount['total_order'];
            $total = count($archiveOrder);
            return array(
                'total_order' => isset($total) ? $total : 0
            );
        } else {
            throw new \Exception('Type is not found', 404);
        }
    }

    public function get($id) {
        $userFunctions = new UserFunctions();
        $userOrderModel = new UserOrder();
        $preOrderModel = new PreOrder();
        $preOrderItemAddonsModel = new PreOrderAddons();
        $session = $this->getUserSession();
        $isLoggedIn = $session->isLoggedIn();
        if ($isLoggedIn) {
            $userId = $session->getUserId();
        } else {
            throw new \Exception('User detail not found', 404);
        }
        $locationData = $session->getUserDetail('selected_location');
        $currentDate = $userFunctions->userCityTimeZone($locationData);
        $config = $this->getServiceLocator()->get('Config');
        $pointEqualDollar = $config ['constants']['pointEqualDollar'];
        $point = $pointEqualDollar[0];
        $dollar = $pointEqualDollar[1];
        /**
         * Get User Order Details Using Order Id
         */
        $userOrder = $userOrderModel->getUserOrder(array(
            'columns' => array(
                'id',
                'created_at',
                'status',
                'order_type1',
                'delivery_time',
                'delivery_charge',
                'tax',
                'tip_amount',
                'order_type',
                'delivery_address',
                'address',
                'apt_suite',
                'deal_discount',
                'deal_title',
                'order_amount',
                'card_type',
                'card_number',
                'expired_on',
                'special_checks',
                'user_comments',
                'restaurant_id',
                'city',
                'state_code',
                'zipcode',
                'tip_percent',
                'card_number',
                'card_type',
                'expired_on',
                'promocode_discount',
                'pay_via_point',
                'pay_via_card',
                'redeem_point',
                'total_amount',
                'receipt_no' => 'payment_receipt',
                'cod'
            ),
            'where' => array(
                'id' => $id,
                'user_id'=>$userId
            )
        ));
        //ALTER TABLE  `user_orders` CHANGE  `redeemed_point`  `redeemed_point` DECIMAL( 10, 2 ) NULL DEFAULT  '0.00' COMMENT 'discount amount against point redeem';
        if (!empty($userOrder) && $userOrder != null) {
            $userOrderData = $userOrder->getArrayCopy();
            
            //pr((int)$userOrderData['redeemed_point'],1);
            $userOrderData['promocode_discount'] = number_format($userOrderData['promocode_discount'], 2, '.', '');
            $userOrderData['deal_discount'] = number_format($userOrderData['deal_discount'], 2, '.', '');

            $userFunctionsModel = new UserFunctions();
            $data = $userFunctionsModel->checkRestaurantStatus($userOrderData['restaurant_id'], $userOrderData['delivery_time']);
            if($userOrderData['status'] == "cancelled" || $userOrderData['status'] == "archived" || $userOrderData['status']=="rejected"){
                 $userOrderData['flag'] = 1;
            }else{
                if ($data == true) {
                    $userOrderData['flag'] = 1;
                } else {
                    $userOrderData['flag'] = 0;
                }
            }
            $userOrderData['delivery_address'] = $userOrderData['delivery_address'] . ", " . $userOrderData['city'] . ", " . $userOrderData['state_code'] . ", " . $userOrderData['zipcode'];

            if (isset($userOrderData) && !empty($userOrderData)) {
                if ($userOrderData['expired_on']) {
                    $months = explode('/', $userOrderData['expired_on']);
                    $year = substr($months[1], - 2, 2);
                    $userOrderData['expired_on'] = $months[0] . '/' . $year;
                } else {
                    $userOrderData['expired_on'] = '';
                }
                $userOrderData['cod'] = (int)$userOrderData['cod'];
                $userOrderData['pay_via_cash'] = ($userOrderData['cod'] == 1)?(string)  number_format($userOrderData['total_amount']- $userOrderData['pay_via_point'],2):"0.00";

                $userOrderData['card_type'] = strtoupper($userOrderData['card_type']);
                $userOrderData['order_type'] = ucfirst($userOrderData['order_type']);
                if ($userOrderData['order_type1'] == 'I') {
                    $userOrderData['order_type1'] = 'Individual';
                } else {
                    $userOrderData['order_type1'] = 'Group';
                }
                $restaurantModel = new Restaurant();
                $restaurantData = $restaurantModel->findByRestaurantId(array(
                    'columns' => array(
                        'city_id',
                        'order_pass_through',
                        'accept_cc_phone',
                        'closed',
                        'inactive',
                        'order_pass_through',
                        'delivery_area',
                        'delivery_charge',
                        'reservations',
                        'dining',
                        'takeout',
                        'minimum_delivery_amount' => 'minimum_delivery',
                        'menu_available',
                        'menu_without_price',
                        'is_delivery' => 'delivery',
                        'res_latitude' =>'latitude' ,
                        'res_longitude' =>'longitude',
                    ),
                    'where' => array(
                        'id' => $userOrderData['restaurant_id']
                    )
                ));
                $userOrderData['accept_cc_phone'] = $restaurantData->accept_cc_phone;
                $userOrderData['delivery_charge'] = $restaurantData->delivery_charge;                              
                
                $userOrderData['city_id'] = $restaurantData->city_id;
                $userOrderData['closed']=$restaurantData->closed;
                $userOrderData['inactive']=$restaurantData->inactive;
                $cityModel = new City();
                $cityDetails = $cityModel->fetchCityDetails($restaurantData->city_id);
                $userOrderData['city_name'] = $cityDetails['city_name'];
                $userOrderDetailModel = new UserOrderDetail();
                
                $joins [] = array(
                    'name' => array(
                        'm' => 'menus'
                    ),
                'on' => new \Zend\Db\Sql\Expression("(m.id = user_order_details.item_id)"),
                'columns' => array(
                   'status','online_order_allowed'                   
                ),
                'type' => 'left'
                );
                $userOrderItem = $userOrderDetailModel->getAllOrderDetail(array(
                    'columns' => array(
                        'id',
                        'item' => 'item',
                        'item_id',
                        'item_price_id',
                        'quantity' => 'quantity',
                        'unit_price' => 'unit_price',
                        'total_item_amt' => 'total_item_amt',
                        'special_instruction' => 'special_instruction',
                        'item_price_desc' => 'item_price_desc'
                    ),
                    'joins'=>$joins,
                    'where' => array(
                        'user_order_id' => $id
                    )
                ));
                
                $userOrderData['order_Type'] = $userOrderData['order_type1'] . ' ' . $userOrderData['order_type'];
                if ($userOrderData['created_at'] != null) {
                    $createDate = StaticOptions::getFormattedDateTime($userOrderData['created_at'], 'Y-m-d H:i:s', 'M d, Y');
                    $createTime = StaticOptions::getFormattedDateTime($userOrderData['created_at'], 'Y-m-d H:i:s', 'h:i A');
                    $userOrderData['time_of_order'] = $createDate . ' at ' . $createTime;
                }
                if ($userOrderData['delivery_time'] != null) {
                    $deliveryDate = StaticOptions::getFormattedDateTime($userOrderData['delivery_time'], 'Y-m-d H:i:s', 'M d, Y');
                    $deliveryTime = StaticOptions::getFormattedDateTime($userOrderData['delivery_time'], 'Y-m-d H:i:s', 'h:i A');
                    $userOrderData['time_of_delivery'] = $deliveryDate . ' at ' . $deliveryTime;
                }

                $userOrderData['order_details'] = $userOrderItem;
                $userOrderData['card_type'] = strtoupper($userOrderData['card_type']);
                $expiredOn = $userOrderData['expired_on'];
                if (!empty($expiredOn)){
                    $months = explode('/', $expiredOn);
                    $year = substr($months[1], - 2, 2);
                    $userOrderData['expired_on'] = $months[0] . '/' . $year;
                }

                /**
                 * Get User Order Item Addons Using User Order Item Id
                 */
                $userOrderAddonsModel = new UserOrderAddons();
                $i = 0;
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
               
                $menuStatus = 0;
                foreach ($userOrderData['order_details'] as $key1 => $value) {
                    if($value['status'] == 1 && $value['online_order_allowed']==1){
                       $menuStatus = 1; 
                    }
                    $orderItemId = $userOrderData['order_details'][$i]['id'];

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
                            'user_order_detail_id' => $orderItemId
                        ),
                        'joins'=>$joins_addons
                    ));
                    $cc = array();
                    $j = 0;

                    foreach ($addon as $key => $result) {

                        //if ($result['addons_option'] == 'None') {
                        //    continue;
                        //} else {
                            $addons['addon_name'] = $userFunctions->to_utf8($result['addons_option']);
                            $addons['addon_price'] = $result['price'];
                            $addons['menu_addons_id'] = $result['menu_addons_id'];
                            $addons['menu_addons_option_id'] = $result['menu_addons_option_id'];
                            $addons['addon_quantity'] = $result['quantity'];
                            $addons['addon_total'] = number_format($result['price'] * $result['quantity'], 2);
                            $addons['priority'] = $result['priority'];
                            $addons['was_free'] = $result['was_free'];
                            $addons['addon_status'] = ($result['addon_status'])?1:0;
//                            if($result['selection_type']==0 && $result['addon_status']==0){
//                                $menuStatus = 0;
//                            }else{
//                                $menuStatus = 1;
//                            }
                            $cc[$j] = $addons;
                            $j++;
                       // }
                    }
                    
                    $totalQuantity = $userOrderData['order_details'][$i]['quantity'];
                    $totalUnitPrice = $userOrderData['order_details'][$i]['unit_price'];
                    $totalPrice = number_format($totalQuantity * $totalUnitPrice, 2);
                    $userOrderData['order_details'][$i]['total_price'] = $totalPrice;
                    $userOrderData['order_details'][$i]['item'] = $userFunctions->to_utf8($userOrderData['order_details'][$i]['item']);
                    $userOrderData['order_details'][$i]['status'] = ($userOrderData['order_details'][$i]['status'])?1:0;
                    $userOrderData['order_details'][$i]['online_order_allowed'] = ($userOrderData['order_details'][$i]['online_order_allowed'])?1:0;
                    if (!empty($cc)) {
                        $userOrderData['order_details'][$i]['addon'] = $cc;
                        $cc = array();
                    } else {
                        $userOrderData['order_details'][$i]['addon'] = array();
                    }
                    $i ++;
                }
                $orderSubTotal = 0;
                $orderSubTotal = (float) $userOrderData['order_amount'];
                $userOrderData['menu_status'] = $menuStatus;
                $orderTax = (float) $userOrderData['tax'];
                if (is_numeric($orderTax) & $orderTax != 0) {
                    $userOrderData['tax'] = number_format($orderTax, 2);
                    $orderSubTotal = $orderSubTotal + $orderTax;
                }
                $orderTip = (float) $userOrderData['tip_amount'];
                if (is_numeric($orderTip) & $orderTip != 0) {
                    $userOrderData['tip_amount'] = number_format($orderTip, 2);
                    $orderSubTotal = $orderSubTotal + $orderTip;
                }
                $orderDelCharge = (float) $userOrderData['delivery_charge'];
                if (is_numeric($orderDelCharge) & $orderDelCharge != 0) {
                    $userOrderData['delivery_charge'] = number_format($orderDelCharge, 2);
                    $orderSubTotal = $orderSubTotal + $orderDelCharge;
                }
                $orderDiscount = (float) $userOrderData['deal_discount'];
                if (is_numeric($orderDiscount) & $orderDiscount != 0) {
                    $userOrderData['deal_discount'] = number_format($orderDiscount, 2);
                    $orderSubTotal = $orderSubTotal - $orderDiscount;
                }
                $userOrderData['order_amount'] = number_format($userOrderData['order_amount'], 2, '.', ',');

                $userOrderData['order_total'] = number_format($orderSubTotal, 2, '.', ',');
                $userOrderData['orderpoints'] = (string) (int) $orderSubTotal;
                $userOrderData['type'] = 'Schedule-Orders';
                $userPoints = '';
                $userId = $session->getUserId();
                if ($userId) {
                    $userModel = new User();
                    $userData = $userModel->getUserDetail(array(
                        'column' => array(
                            'points'
                        ),
                        'where' => array(
                            'id' => $userId
                        )
                            ));
                    $userPoints = $userData ['points'];
                }
                $userOrderData['points'] = $userPoints;

                return $userOrderData;
            }
        }else{
             throw new \Exception('Order details not found');
        } /*
         * else { $preOrder = $preOrderModel->getPreOrderDetails($id); if (! empty($preOrder) && $preOrder != null) { $preOrderData = $preOrder->getArrayCopy(); if (isset($preOrderData) && ! empty($preOrderData)) { if ($preOrderData['order_type1'] == 'I') { $preOrderData['order_type1'] = 'Individual'; } else { $preOrderData['order_type1'] = 'Group'; } /** Get User Pre Order Item Details Using Pre Order Id
         */

        /* $preOrderItemModel = new PreOrderItem();
          $preOrderItem = $preOrderItemModel->getPreOrder(array(
          'columns' => array(
          'id',
          'item' => 'item',
          'quantity' => 'quantity',
          'total_item_amt' => 'total_item_amt',
          'special_instruction' => 'special_instruction'
          ),
          'where' => array(
          'pre_order_id' => $id
          )
          ));
          $preOrderData['order_Type'] = $preOrderData['order_type1'] . ' ' . $preOrderData['order_type'];
          if ($preOrderData['created_at'] != null) {
          $createDate = StaticOptions::getFormattedDateTime($preOrderData['created_at'], 'Y-m-d H:i:s', 'M d, Y');
          $createTime = StaticOptions::getFormattedDateTime($preOrderData['created_at'], 'Y-m-d H:i:s', 'h:i A');
          $preOrderData['time_of_order'] = $createDate . ' at ' . $createTime;
          }

          if ($preOrderData['delivery_time'] != null) {
          $deliveryDate = StaticOptions::getFormattedDateTime($preOrderData['delivery_time'], 'Y-m-d H:i:s', 'M d, Y');
          $deliveryTime = StaticOptions::getFormattedDateTime($preOrderData['delivery_time'], 'Y-m-d H:i:s', 'h:i A');
          $preOrderData['time_of_delivery'] = $deliveryDate . ' at ' . $deliveryTime;
          }

          $preOrderData['order_details'] = $preOrderItem;

          /**
         * Get User Pre Order Item Addons Using Pre Order Item Id
         */
        /*  $i = 0;
          foreach ($preOrderData['order_details'] as $key1 => $value) {

          $preOrderItemId = $preOrderData['order_details'][$i]['id'];

          $addon = $preOrderItemAddonsModel->getUserPreOrderAddons(array(
          'columns' => array(
          'addons_option',
          'price',
          'quantity'
          ),
          'where' => array(
          'pre_order_item_id' => $preOrderItemId
          )
          ));
          $cc = array();

          foreach ($addon as $key => $result) {

          $addons['addon_name'] = $result['addons_option'];
          $addons['addon_price'] = $result['price'];
          $addons['addon_quantity'] = $result['quantity'];
          $addons['addon_total'] = number_format($result['price'] * $result['quantity'], 2);
          $cc[$key] = $addons;
          }

          if (! empty($cc)) {
          $preOrderData['order_details'][$i]['addon'] = $cc;
          $cc = array();
          } else {
          $preOrderData['order_details'][$i]['addon'] = array();
          }
          $i ++;
          }
          $orderSubTotal = 0;
          $orderSubTotal = (float) $preOrderData['sub_total'];

          $orderTax = (float) $preOrderData['tax'];
          if (is_numeric($orderTax) & $orderTax != 0) {
          $preOrderData['tax'] = number_format($orderTax, 2);
          $orderSubTotal = $orderSubTotal + $orderTax;
          }
          $orderTip = (float) $preOrderData['tip'];
          if (is_numeric($orderTip) & $orderTip != 0) {
          $preOrderData['tip_amount'] = number_format($orderTip, 2);
          $orderSubTotal = $orderSubTotal + $orderTip;
          }
          $orderDelCharge = (float) $preOrderData['delivery_charges'];
          if (is_numeric($orderDelCharge) & $orderDelCharge != 0) {
          $preOrderData['delivery_charge'] = number_format($orderDelCharge, 2);
          $orderSubTotal = $orderSubTotal + $orderDelCharge;
          }
          $orderDiscount = (float) $preOrderData['discount'];
          if (is_numeric($orderDiscount) & $orderDiscount != 0) {
          $preOrderData['discount'] = number_format($orderDiscount, 2);
          $orderSubTotal = $orderSubTotal - $orderDiscount;
          }
          $preOrderData['order_amount'] = number_format($preOrderData['sub_total'], 2, '.', ',');

          $preOrderData['order_total'] = number_format($orderSubTotal, 2, '.', ',');
          $preOrderData['type'] = 'Pre-Schedule';
          }
          return $preOrderData;
          } else {
          throw new \Exception('Order Id Not Found', 404);
          }
          } */
    }

    public function update($id, $data) {
        $userFunction = new UserFunctions();
        $userNotificationModel = new UserNotification();
        $session = $this->getUserSession();
        $isLoggedIn = $session->isLoggedIn();
        $locationData = $session->getUserDetail('selected_location');
        $currentDate = $userFunction->userCityTimeZone($locationData);

        if ($isLoggedIn)
            $userId = $session->getUserId();

        $userOrderModel = new UserOrder();
        $type = $data['type'];
        $token = $data['token'];
        if ($type == 'cancel') {
            // check existing order in user order
            $response = $userOrderModel->getUserOrder(array(
                'columns' => array(
                    'id',
                    'restaurant_id',
                    'user_id'
                ),
                'where' => array(
                    'id' => $id
                )
            ));
            if ($response) {
                $userOrder = $userOrderModel->cancelOrder($id, $userId);
                $userOrder = $this->sendCancelOrderMailToUser($id, $userId);
            } else {
                // check existing order in pre order
                $preOrderModel = new PreOrder();
                $response = $preOrderModel->getUserPreOrder(array(
                    'columns' => array(
                        'id',
                        'restaurant_id',
                        'user_id'
                    ),
                    'where' => array(
                        'id' => $id
                    )
                ));
                if ($response) {
                    $userOrder = $preOrderModel->cancelPreOrder($id, $userId);
                    $msg = 'You order was successfully canceled. Bummer.';
                    $channel = "mymunchado_" . $token;
                    $notificationArray = array(
                        "msg" => $msg,
                        "channel" => $channel,
                        "userId" => $response['user_id'],
                        "type" => 'order',
                        "restaurantId" => $response['restaurant_id'],
                        'curDate' => $currentDate
                    );
                    $response = $userNotificationModel->createPubNubNotification($notificationArray);
                    $pubnub = StaticOptions::pubnubPushNotification($notificationArray);
                } else {
                    throw new \Exception("Invalid order id provided", 500);
                }
            }
            return $userOrder;
        }
    }

    private function sendCancelOrderMailToUser($id, $userId) {
        $userOrderModel = new UserOrder();
        $restaurnatModel = new Restaurant();
        $userFunctions = new UserFunctions();
        $userOrderDetailsModel = new UserOrderDetail();
        $order = $userOrderModel->getUserOrder(array(
            'columns' => array(
                'id',
                'fname',
                'email',
                'restaurant_id',
                'city',
                'apt_suite',
                'created_at',
                'zipcode',
                'status',
                'payment_receipt',
                'order_type1',
                'order_type',
                'delivery_time',
                'delivery_charge',
                'tax',
                'tip_amount',
                'order_type',
                'delivery_address',
                'deal_discount',
                'order_amount',
                'card_type',
                'card_number',
                'expired_on',
                'special_checks',
                'user_comments',
                'special_checks',
                'user_comments',
                'promocode_amount'
            ),
            'where' => array(
                'id' => $id
            )
        ));
        $userOrderData = $order->getArrayCopy();

        $firstName = $userOrderData['fname'];
        $email = $userOrderData['email'];
        /*
         * $city = $userOrderData['city']; $aptSuite = $userOrderData['apt_suite']; $zipcode = $userOrderData['zipcode']; $subTotal = $userOrderData['order_amount']; $tipAmount = $userOrderData['tip_amount']; $paymentReceipt =$userOrderData['payment_receipt']; $tax = $userOrderData['tax']; $deliveryCharge= $userOrderData['delivery_charge']; $specialChecks=''; $specialChecks =$userOrderData['special_checks']; if(!empty($specialChecks)){ $specialChecks = explode('||',$specialChecks); if(count($specialChecks)>0){ $specialChecks = implode(', ',$specialChecks); } } $userComments = $userOrderData['user_comments']; if(!empty($userComments)){ $specialChecks=$specialChecks.'<br />'.$userComments; } $typeOfOrder = $userOrderData['order_type']; if($typeOfOrder == 'Delivery'){ $total= (float)$subTotal + (float)$tipAmount + (float)$tax + (float)$deliveryCharge; }else{ $total= (float)$subTotal + (float)$tipAmount + (float)$tax ; }
         */
        $deliveryTime = $userOrderData['delivery_time'];
        /*
         * $deliveryAddress = $userOrderData['delivery_address']; $deliveryTo=$deliveryAddress; if(!empty($aptSuite)){ $deliveryTo=$aptSuite.', '.$deliveryTo; } if(!empty($zipcode)){ $deliveryTo=$deliveryTo.' - '.$zipcode; } $cardNumber = $userOrderData['card_number']; $cardType = $userOrderData['card_type']; $expiredOn = $userOrderData['expired_on']; $orderType1 = $userOrderData['order_type1']; if($orderType1=='G'){ $orderType ='Group '.$typeOfOrder; }else{ $orderType ='Individual '.$typeOfOrder; }
         */

        $restaurant = $restaurnatModel->findRestaurant(array(
            'columns' => array(
                'restaurant_name'
            ),
            'where' => array(
                'id' => $userOrderData['restaurant_id']
            )
        ));
        $restaurantName = $restaurant->restaurant_name;
        /*
         * $orderData = $userOrderDetailsModel->getOrderDetailItems($id); $orderStringData = $this->orderDataItemForMail($orderData);
         */

        $dateToDelivery = StaticOptions::getFormattedDateTime($deliveryTime, 'Y-m-d H:i:s', 'D, M d, Y');
        $timeToDelivery = StaticOptions::getFormattedDateTime($deliveryTime, 'Y-m-d H:i:s', 'h:i A');
        $toDelivery = $dateToDelivery . " at " . $timeToDelivery;
        /*
         * $deliveryCharge = $userFunctions->convertToDecimal($deliveryCharge); $tax = $userFunctions->convertToDecimal($tax); $tipAmount = $userFunctions->convertToDecimal($tipAmount); $subTotal = $userFunctions->convertToDecimal($subTotal); $total = $userFunctions->convertToDecimal($total);
         */
        $config = $this->getServiceLocator()->get('Config');
        $webUrl = PROTOCOL . $config['constants']['web_url'];
        $template = "email-template/send-cancel-order-user";
        $layout = 'email-layout/default';
        $sender = EMAIL_FROM;
        $recievers = array(
            $email
        );
        $sendername = '';
        $subject = "Your Pre Scheduled Order Has Been Pre Canceled";
        $variables = array(
            'name' => $firstName,
            'restaurant_name' => $restaurantName,
            'reply_to' => EMAIL_FROM,
            'timetodelivery' => $toDelivery,
            'host_name' => $webUrl
        );
        StaticOptions::sendMail($sender, $sendername, $recievers, $template, $layout, $variables, $subject);

        return array(
            'success' => true
        );
    }

    public function orderDataItemForMail($orderData) {
        $userOrderAddonsModel = new UserOrderAddons();
        $orderString = '';
        $orderString .= ' <tr>
                    <td height="30" colspan="3" align="left" style="font-size:13px; color:#666666;" >
                      <strong>Your Order</strong>
                    </td>
                  </tr>';
        $i = 1;
        $itemCount = count($orderData);
        foreach ($orderData as $key => $item) {

            $orderString .= '<tr>
                    <td align="left" valign="top" style="color:#666666;" width="160" ><strong>' . $item['item'] . '</strong></td>
                    <td align="center" valign="top" style="color:#666666;" width="30" >' . $item['quantity'] . '</td>
                    <td align="right" valign="top" style="color:#666666;" width="119" ><strong>$' . number_format($item['unit_price'] * $item['quantity'], 2) . '</strong></td>
                  </tr>';
            if (!empty($item['special_instruction'])) {
                $orderString .= '<tr>
                    <td height="20" colspan="3" align="left" valign="top" style="color:#666666;" >
                      <i>' . $item['special_instruction'] . '</i>
                    </td>
                  </tr>';
            }

            $addon = $userOrderAddonsModel->getAllOrderAddon(array(
                'columns' => array(
                    'addons_option',
                    'price',
                    'quantity'
                ),
                'where' => array(
                    'user_order_detail_id' => $item['id']
                )
            ));

            if (!empty($addon)) {

                foreach ($addon as $addkey => $addon) {
                    if ($addon['addons_option'] != 'None') {
                        $orderString .= '<tr>
                            <td align="left" valign="top" style="color:#666666;" width="160" >&nbsp;&nbsp;+ ' . $addon['addons_option'] . '</td>
                            <td align="center" valign="top" style="color:#666666; font-size:11px;" width="30" >' . $addon['quantity'] . '</td>
                            <td align="right" valign="top" style="color:#666666;font-size:11px;" width="119" >$' . number_format($addon['price'] * $addon['quantity'], 2) . '</td>
                          </tr>';
                    }
                }

                $orderString .= '<tr>
                    <td align="left" valign="top" style="color:#666666;font-size:11px;" width="160" >&nbsp;&nbsp;</td>
                    <td align="center" valign="top" style="color:#666666;font-size:11px;" width="30" >&nbsp;</td>
                    <td align="right" valign="top" height="25" style="color:#666666;font-size:12px;" width="119" ><strong>$' . number_format($item['total_item_amt'], 2) . '</strong></td>
                  </tr> ';
            }

            if ($itemCount != $i) {
                $orderString .= '<tr>
                                  <td height="15" colspan="3" align="left" style="color:#666666;"><img src="' . '' . 'border.png" width="309" height="1" /></td>
                                  </tr>';
            }
            $i ++;
        }

        return $orderString;
    }

    public function cronOrderDetails($id = false, $paramUserId = false) {
        $userFunctions = new UserFunctions();
        $userOrderModel = new UserOrder();
        //$session = $this->getUserSession();
        //$locationData = $session->getUserDetail('selected_location');
        //$currentDate = $userFunctions->userCityTimeZone($locationData);
        /**
         * Get User Order Details Using Order Id
         */
        $userOrder = $userOrderModel->getUserOrder(array(
            'columns' => array(
                'id',
                'created_at',
                'status',
                'order_type1',
                'delivery_time',
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
                'expired_on',
                'special_checks',
                'user_comments',
                'restaurant_id',
                'city',
                'state_code',
                'zipcode',
                'tip_percent',
                'card_number',
                'card_type',
                'expired_on',
                'promocode_discount',
                'total_amount',
                'receipt_no' => 'payment_receipt',
            ),
            'where' => array(
                'id' => $id
            )
        ));

        if (!empty($userOrder) && $userOrder != null) {
            $userOrderData = $userOrder->getArrayCopy();
            $userOrderData['promocode_discount'] = number_format($userOrderData['promocode_discount'], 2, '.', '');
            $userOrderData['deal_discount'] = number_format($userOrderData['deal_discount'], 2, '.', '');

            $userFunctionsModel = new UserFunctions();
            $data = $userFunctionsModel->checkRestaurantStatus($userOrderData['restaurant_id'], $userOrderData['delivery_time']);
            
            if ($data == true) {
                $userOrderData['flag'] = 1;
            } else {
                $userOrderData['flag'] = 0;
            }
            
            $userOrderData['delivery_address'] = $userOrderData['delivery_address'] . ", " . $userOrderData['city'] . ", " . $userOrderData['state_code'] . ", " . $userOrderData['zipcode'];

            if (isset($userOrderData) && !empty($userOrderData)) {
                if ($userOrderData['expired_on']) {
                    $months = explode('/', $userOrderData['expired_on']);
                    $year = substr($months[1], - 2, 2);
                    $userOrderData['expired_on'] = $months[0] . '/' . $year;
                } else {
                    $userOrderData['expired_on'] = '';
                }

                $userOrderData['card_type'] = strtoupper($userOrderData['card_type']);
                $userOrderData['order_type'] = ucfirst($userOrderData['order_type']);
                if ($userOrderData['order_type1'] == 'I') {
                    $userOrderData['order_type1'] = 'Individual';
                } else {
                    $userOrderData['order_type1'] = 'Group';
                }
                $restaurantModel = new Restaurant();
                $restaurantData = $restaurantModel->findByRestaurantId(array(
                    'columns' => array(
                        'city_id',
                        'order_pass_through'
                    ),
                    'where' => array(
                        'id' => $userOrderData['restaurant_id']
                    )
                ));
                $userOrderData['city_id'] = $restaurantData->city_id;
                $cityModel = new City();
                $cityDetails = $cityModel->fetchCityDetails($restaurantData->city_id);
                $userOrderData['city_name'] = $cityDetails['city_name'];
                $userOrderDetailModel = new UserOrderDetail();
                $userOrderItem = $userOrderDetailModel->getAllOrderDetail(array(
                    'columns' => array(
                        'id',
                        'item' => 'item',
                        'item_id',
                        'item_price_id',
                        'quantity' => 'quantity',
                        'unit_price' => 'unit_price',
                        'total_item_amt' => 'total_item_amt',
                        'special_instruction' => 'special_instruction',
                        'item_price_desc' => 'item_price_desc'
                    ),
                    'where' => array(
                        'user_order_id' => $id
                    )
                ));
                $userOrderData['order_Type'] = $userOrderData['order_type1'] . ' ' . $userOrderData['order_type'];
                if ($userOrderData['created_at'] != null) {
                    $createDate = StaticOptions::getFormattedDateTime($userOrderData['created_at'], 'Y-m-d H:i:s', 'M d, Y');
                    $createTime = StaticOptions::getFormattedDateTime($userOrderData['created_at'], 'Y-m-d H:i:s', 'h:i A');
                    $userOrderData['time_of_order'] = $createDate . ' at ' . $createTime;
                }
                if ($userOrderData['delivery_time'] != null) {
                    $deliveryDate = StaticOptions::getFormattedDateTime($userOrderData['delivery_time'], 'Y-m-d H:i:s', 'M d, Y');
                    $deliveryTime = StaticOptions::getFormattedDateTime($userOrderData['delivery_time'], 'Y-m-d H:i:s', 'h:i A');
                    $userOrderData['time_of_delivery'] = $deliveryDate . ' at ' . $deliveryTime;
                }

                $userOrderData['order_details'] = $userOrderItem;
                $userOrderData['card_type'] = strtoupper($userOrderData['card_type']);
                $expiredOn = $userOrderData['expired_on'];
                if (!empty($expiredOn)) {
                    $months = explode('/', $expiredOn);
                    $year = substr($months[1], - 2, 2);
                    $userOrderData['expired_on'] = $months[0] . '/' . $year;
                }

                /**
                 * Get User Order Item Addons Using User Order Item Id
                 */
                $userOrderAddonsModel = new UserOrderAddons();
                $i = 0;
                foreach ($userOrderData['order_details'] as $key1 => $value) {

                    $orderItemId = $userOrderData['order_details'][$i]['id'];

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
                            'user_order_detail_id' => $orderItemId
                        )
                    ));
                    $cc = array();
                    $j = 0;

                    foreach ($addon as $key => $result) {

                        if ($result['addons_option'] == 'None') {
                            continue;
                        } else {
                            $addons['addon_name'] = $userFunctions->to_utf8($result['addons_option']);
                            $addons['addon_price'] = $result['price'];
                            $addons['menu_addons_id'] = $result['menu_addons_id'];
                            $addons['menu_addons_option_id'] = $result['menu_addons_option_id'];
                            $addons['addon_quantity'] = $result['quantity'];
                            $addons['addon_total'] = number_format($result['price'] * $result['quantity'], 2);
                            $addons['priority'] = $result['priority'];
                            $addons['was_free'] = $result['was_free'];
                            $cc[$j] = $addons;
                            $j++;
                        }
                    }
                    $totalQuantity = $userOrderData['order_details'][$i]['quantity'];
                    $totalUnitPrice = $userOrderData['order_details'][$i]['unit_price'];
                    $totalPrice = number_format($totalQuantity * $totalUnitPrice, 2);
                    $userOrderData['order_details'][$i]['total_price'] = $totalPrice;
                    $userOrderData['order_details'][$i]['item'] = $userFunctions->to_utf8($userOrderData['order_details'][$i]['item']);

                    if (!empty($cc)) {
                        $userOrderData['order_details'][$i]['addon'] = $cc;
                        $cc = array();
                    } else {
                        $userOrderData['order_details'][$i]['addon'] = array();
                    }
                    $i ++;
                }
                $orderSubTotal = 0;
                $orderSubTotal = (float) $userOrderData['order_amount'];

                $orderTax = (float) $userOrderData['tax'];
                if (is_numeric($orderTax) & $orderTax != 0) {
                    $userOrderData['tax'] = number_format($orderTax, 2);
                    $orderSubTotal = $orderSubTotal + $orderTax;
                }
                $orderTip = (float) $userOrderData['tip_amount'];
                if (is_numeric($orderTip) & $orderTip != 0) {
                    $userOrderData['tip_amount'] = number_format($orderTip, 2);
                    $orderSubTotal = $orderSubTotal + $orderTip;
                }
                $orderDelCharge = (float) $userOrderData['delivery_charge'];
                if (is_numeric($orderDelCharge) & $orderDelCharge != 0) {
                    $userOrderData['delivery_charge'] = number_format($orderDelCharge, 2);
                    $orderSubTotal = $orderSubTotal + $orderDelCharge;
                }
                $orderDiscount = (float) $userOrderData['deal_discount'];
                if (is_numeric($orderDiscount) & $orderDiscount != 0) {
                    $userOrderData['deal_discount'] = number_format($orderDiscount, 2);
                    $orderSubTotal = $orderSubTotal - $orderDiscount;
                }
                $userOrderData['order_amount'] = number_format($userOrderData['order_amount'], 2, '.', ',');

                $userOrderData['order_total'] = number_format($orderSubTotal, 2, '.', ',');
                $userOrderData['orderpoints'] = (string) (int) $orderSubTotal;
                $userOrderData['type'] = 'Schedule-Orders';
                $userPoints = '';
                $userId = $paramUserId;
                if ($userId) {
                    $userModel = new User();
                    $userData = $userModel->getUserDetail(array(
                        'column' => array(
                            'points'
                        ),
                        'where' => array(
                            'id' => $userId
                        )
                            ));
                    $userPoints = $userData ['points'];
                }
                $userOrderData['points'] = $userPoints;

                return $userOrderData;
            }
        }
    }

}
