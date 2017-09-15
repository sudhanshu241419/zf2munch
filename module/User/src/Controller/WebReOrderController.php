<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\UserFunctions;
use User\Model\UserOrder;
use MCommons\StaticOptions;
use User\Model\UserOrderDetail;
use User\Model\UserOrderAddons;
use Restaurant\Model\Restaurant;
use User\Model\User;
use Restaurant\Model\City;

class WebReOrderController extends AbstractRestfulController {

    public $orderId;

    public function get($id) {
        $userFunctions = new UserFunctions();
        $session = $this->getUserSession();
        if (!isset($id) && empty($id)) {
            throw new \Exception("Order details not found", 405);
        }
        $this->orderId = $id;
        $userOrder = $this->getUserOrderDetails();
        if (empty($userOrder)) {
            throw new \Exception("Order details not found", 405);
        }

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
        //pr($userOrderData,1);
        $this->getRestaurantDetails($userOrderData);
        $this->getCity($userOrderData);
        $this->orderItemDetails($userOrderData);


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

    public function getUserOrderDetails() {
        $userOrderModel = new UserOrder();
        return $userOrderModel->getUserOrder(array(
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
                        'pay_via_point',
                        'pay_via_card',
                        'redeem_point',
                        'total_amount',
                        'receipt_no' => 'payment_receipt',
                    ),
                    'where' => array(
                        'id' => $this->orderId
                    )
        ));
    }

    public function getRestaurantDetails(&$userOrderData) {
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
        $userOrderData['closed'] = $restaurantData->closed;
        $userOrderData['inactive'] = $restaurantData->inactive;
    }

    public function getCity(&$userOrderData) {
        $cityModel = new City();
        $cityDetails = $cityModel->fetchCityDetails($userOrderData['city_id']);
        $userOrderData['city_name'] = $cityDetails['city_name'];
    }

    public function orderItemDetails(&$userOrderData) {
        $userOrderDetailModel = new UserOrderDetail();

        $joins [] = array(
            'name' => array(
                'm' => 'menus'
            ),
            'on' => new \Zend\Db\Sql\Expression("(m.id = user_order_details.item_id)"),
            'columns' => array(
                'status', 'online_order_allowed'
            ),
            'type' => 'left'
        );
        $joins [] = array(
            'name' => array(
                'mp' => 'menu_prices'
            ),
            'on' => new \Zend\Db\Sql\Expression("(user_order_details.item_id=mp.menu_id)"),
            'columns' => array(
                'item_price_id'=>'id',
                'unit_price' => new \Zend\Db\Sql\Expression('IF(mp.price IS NULL,0,round(mp.price,2))'),
                'item_price_desc'=>'price_desc'
            ),
            'type' => 'left'
        );
        $userOrderItem = $userOrderDetailModel->getAllOrderDetail(array(
            'columns' => array(
                'id',
                'item',
                'item_id',
                'quantity',                
                'special_instruction' => 'special_instruction',
            ),
            'joins' => $joins,
            'where' => array(
                'user_order_id' => $this->orderId
            )
        ));
        
        foreach($userOrderItem as $key =>$val){
          $userOrderItem[$key]['total_item_amt'] = number_format($userOrderItem[$key]['quantity']*$userOrderItem[$key]['unit_price'],2);
        }
       
        $userOrderData['order_details'] = $userOrderItem;
    }

}
