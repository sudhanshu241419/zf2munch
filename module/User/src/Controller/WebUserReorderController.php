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

class WebUserReorderController extends AbstractRestfulController {

    public function get($id) {
        $userFunctions = new UserFunctions();
        $userOrderModel = new UserOrder();
        $session = $this->getUserSession();
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
                'latitude',
                'longitude',
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
                    ),
                    'where' => array(
                        'id' => $userOrderData['restaurant_id']
                    )
                ));
                $userOrderData['accept_cc_phone'] = $restaurantData->accept_cc_phone;
                $userOrderData['city_id'] = $restaurantData->city_id;
                $userOrderData['closed']=$restaurantData->closed;
                $userOrderData['inactive']=$restaurantData->inactive;
                $cityModel = new City();
                $cityDetails = $cityModel->fetchCityDetails($restaurantData->city_id);
                $userOrderData['city_name'] = $cityDetails['city_name'];

                $menuItemsList = $this->getMenuItems($id);

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

                //$userOrderData['order_details'] = $userOrderItem;
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
                $userOrderData['order_details'] = $menuItemsList;
                $userOrderAddonsModel = new UserOrderAddons();
                $i = 0;
                foreach ($userOrderData['order_details'] as $key1 => $value) {
                    $orderItemId = $value['id'];
                    $addon = $userOrderAddonsModel->getUserOrderAddons($orderItemId);
                    $cc = array();
                    $j = 0;
                    $addonTotalPrice = 0;
                    foreach ($addon as $key => $result) {
                        if ($result['addon_option'] == 'None') {
                            continue;
                        } else {
                            
                            $addonTotal = number_format($result['price'] * $result['quantity'], 2);
                            $addons['addon_name'] = $userFunctions->to_utf8($result['addon_option']);
                            $addons['addon_price'] = $result['price'];
                            $addons['optionPriceDescription'] = $result['price_description'];
                            $addons['menu_addons_id'] = $result['menu_addons_id'];
                            $addons['menu_addons_option_id'] = $result['menu_addons_option_id'];
                            $addons['addon_quantity'] = $result['quantity'];
                            $addons['addon_total'] = $addonTotal;
                            $addons['priority'] = $result['priority'];
                            $addons['was_free'] = $result['was_free'];  
                            $addons['addon_status'] = $result['addon_status'];
                            $cc[$j] = $addons;
                            $j++;
                            $addonTotalPrice = $addonTotal + $addonTotalPrice;
                        }
                    }
                    $totalQuantity = $userOrderData['order_details'][$i]['quantity'];
                    $totalUnitPrice = $userOrderData['order_details'][$i]['unit_price'];
                    $totalPrice = number_format($totalQuantity * $totalUnitPrice, 2);
                    $userOrderData['order_details'][$i]['total_price'] = $totalPrice;
                    $userOrderData['order_details'][$i]['total_item_amt'] = number_format($totalPrice + $addonTotalPrice,2);
                    $userOrderData['order_details'][$i]['item'] = $userFunctions->to_utf8($userOrderData['order_details'][$i]['item']);

                    if (!empty($cc)) {
                        $userOrderData['order_details'][$i]['addon'] = $cc;
                        $cc = array();
                    } else {
                        $userOrderData['order_details'][$i]['addon'] = array();
                    }
                    $i ++;
                }
                   
                return $userOrderData;
            }
        }else{
             throw new \Exception('Order details not found');
        }
    }
    public function getMenuItems($id){
        $userOrderDetailModel = new UserOrderDetail();
                
                $joins [] = array(
                    'name' => array(
                        'm' => 'menus'
                    ),
                'on' => new \Zend\Db\Sql\Expression("(m.id = user_order_details.item_id)"),
                'columns' => array(
                   'item' => 'item_name',
                   'status','online_order_allowed'                   
                ),
                'type' => 'right'
                );
                $joins [] = array(
                    'name' => array(
                        'mp' => 'menu_prices'
                    ),
                    'on' => new \Zend\Db\Sql\Expression("user_order_details.item_id=mp.menu_id and user_order_details.item_price_id=mp.id"),
                    'columns' => array(
                        'item_price_id' => 'id',
                        'unit_price' => new \Zend\Db\Sql\Expression('IF(mp.price IS NULL,0,round(mp.price,2))')                       
                    ),
                    'type' => 'right'
                );
                $userOrderItem = $userOrderDetailModel->getAllOrderDetail(array(
                    'columns' => array(
                        'id',
                        'item_id' => 'item_id',
                        'item_price_id' => 'item_price_id',
                        'quantity' => 'quantity',
                        'unit_price' => 'unit_price',
                        'special_instruction' => 'special_instruction',
                    ),
                    'joins'=>$joins,
                    'where' => array(
                        'user_order_id' => $id
                    )
                ));
                return $userOrderItem;
    }
}
