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

class ReOrderDetailsController extends AbstractRestfulController {

    public function get($id) {
        $userFunctions = new UserFunctions();
        $userOrderModel = new UserOrder();
        $preOrderModel = new \Restaurant\Model\PreOrder();
        $preOrderItemAddonsModel = new \Restaurant\Model\PreOrderAddons();
        $session = $this->getUserSession();
        $locationData = $session->getUserDetail('selected_location');
        $currentDate = $userFunctions->userCityTimeZone($locationData);
        /**
         * Get User Order Details Using Order Id
         */
        $userOrder = $userOrderModel->getUserOrder(array(
            'columns' => array(
                'id',
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
                'total_amount',
                'payment_receipt',
                'street' => 'address',
                'apt_suite',
                'phone',
                'billing_zip',
                'latitude',
                'longitude'
            ),
            'where' => array(
                'id' => $id
            ),
        ));

        if (!empty($userOrder) && $userOrder != null) {

            $userOrderData = $userOrder->getArrayCopy();
            $joins = array();
            $restaurantModel = new \Restaurant\Model\Restaurant();
            $joins [] = array(
                'name' => array(
                    'c' => 'cities'
                ),
                'on' => 'restaurants.city_id = c.id',
                'columns' => array(
                    'city_name',
                    'state_code',
                    'sales_tax',
                ),
                'type' => 'INNER'
            );
            $restaurant = array(
                'columns' => array(
                    'city_id',
                    'order_pass_through',
                    'restaurant_name',
                    'restaurant_address' => 'address',
                    'restaurant_zipcode' => 'zipcode',
                    'rest_code',
                    'restaurant_image_name',
                    'delivery_area',
                    'delivery_charge',
                    'restaurant_id' => 'id',
                    'reservations',
                    'dining',
                    'accept_cc_phone',
                    'takeout',
                    'minimum_delivery_amount' => 'minimum_delivery',
                    'menu_available',
                    'menu_without_price',
                    'is_delivery' => 'delivery',
                    'res_latitude' =>'latitude' ,
                    'res_longitude' =>'longitude',
                    'menu_without_price',                    
                ),
                'where' => array(
                    'restaurants.id' => $userOrderData['restaurant_id']
                ),
                'joins' => $joins
            );
            $restaurantModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
            $restaurantData = $restaurantModel->find($restaurant)->toArray();
            $userOrderData['restaurant_name'] = $restaurantData[0]['restaurant_name'];
            $userOrderData['order_allow'] = (int)0;
            if($userOrderData['order_type']==='Delivery'){
                if($restaurantData[0]['is_delivery'] == 1 && $restaurantData[0]['menu_without_price'] == 0 && $restaurantData[0]['accept_cc_phone'] == 1){
                    $userOrderData['order_allow'] = (int)1;
                }
            }elseif($userOrderData['order_type']==='Takeout'){
                if($restaurantData[0]['takeout'] == 1 && $restaurantData[0]['menu_without_price'] == 0 && $restaurantData[0]['accept_cc_phone'] == 1){
                    $userOrderData['order_allow'] = (int)1;
                }
            }
            // = $restaurantData[0]['restaurant_name'];
            $userOrderData['restaurant_address'] = $restaurantData[0]['restaurant_address'] . ", " . $restaurantData[0]['city_name'] . ", " . $restaurantData[0]['state_code'] . ", " . $restaurantData[0]['restaurant_zipcode'];
            $userOrderData['rest_code'] = strtolower($restaurantData[0]['rest_code']);
            $userOrderData['restaurant_image_name'] = strtolower($restaurantData[0]['restaurant_image_name']);
            $userOrderData['promocode_discount'] = number_format($userOrderData['promocode_discount'], 2, '.', '');
            $userOrderData['deal_discount'] = number_format($userOrderData['deal_discount'], 2, '.', '');
            $userOrderData['my_payment_details']['card_name'] = $userOrderData['name_on_card'];
            $userOrderData['my_payment_details']['card_number'] = $userOrderData['card_number'];
            $userOrderData['my_payment_details']['card_type'] = $userOrderData['card_type'];
            $expired_on = explode("/", $userOrderData['expired_on']);
            $userOrderData['my_payment_details']['expiry_year'] = isset($expired_on[1])?$expired_on[1]:"";
            $userOrderData['my_payment_details']['expiry_month'] = isset($expired_on[0])?$expired_on[0]:"";
            $userOrderData['my_payment_details']['billing_zip'] = $userOrderData['billing_zip'];

            $userOrderData['my_delivery_detail']['first_name'] = $userOrderData['customer_first_name'];
            $userOrderData['my_delivery_detail']['last_Name'] = $userOrderData['customer_last_name'];
            $userOrderData['my_delivery_detail']['email'] = $userOrderData['email'];
            $userOrderData['my_delivery_detail']['city'] = $userOrderData['city'];
            $userOrderData['my_delivery_detail']['apt_suite'] = $userOrderData['apt_suite'];
            $userOrderData['my_delivery_detail']['state'] = $userOrderData['state_code'];
            $userOrderData['my_delivery_detail']['phone'] = $userOrderData['phone'];
            if (!empty($userOrderData['delivery_address'])) {
                $address = $userOrderData['delivery_address'];
            } else {
                $address = $userOrderData['street'];
            }
            $userOrderData['my_delivery_detail']['address'] = $address;
            $userOrderData['my_delivery_detail']['street'] = $userOrderData['street'];
            $userOrderData['my_delivery_detail']['zipcode'] = $userOrderData['zipcode'];
            $userOrderData['my_delivery_detail']['latitude'] = $userOrderData['latitude'];
            $userOrderData['my_delivery_detail']['longitude'] = $userOrderData['longitude'];
            //pr($userOrderData,1);
            $userOrderData ['order_amount_calculation']['subtotal'] = number_format($userOrderData['order_amount'], 2, '.', '');
            $userOrderData ['order_amount_calculation']['tax_amount'] = $userOrderData['tax'];
            $userOrderData ['order_amount_calculation']['tip_amount'] = $userOrderData['tip_amount'];
            $userOrderData ['order_amount_calculation']['delivery_charge'] = $userOrderData['delivery_charge'];
            $userOrderData ['order_amount_calculation']['discount'] = ($userOrderData['deal_discount'] > 0) ? $userOrderData['deal_discount'] : "0";
            $userOrderData ['order_amount_calculation']['promocode_discount'] = ($userOrderData['promocode_discount'] > 0) ? $userOrderData['promocode_discount'] : "0";
            $userOrderData ['order_amount_calculation']['total_order_price'] = $userOrderData['total_amount'];
            $userOrderData['delivery_address'] = $userOrderData['delivery_address'] . ", " . $userOrderData['city'] . ", " . $userOrderData['state_code'] . ", " . $userOrderData['zipcode'];
            ########### Get Deal #################################
            $restaurantFunctions = new \Restaurant\RestaurantDetailsFunctions();
            $deal = array_values($restaurantFunctions->getDealsForRestaurant($userOrderData['restaurant_id']));
            if ($deal) {
                $userOrderData['restaurant']['deals'] = $deal;
                $userOrderData['restaurant']['is_running_deal_coupon'] = intval(1);
            } else {
                $userOrderData['restaurant']['deals'] = array();
                $userOrderData['restaurant']['is_running_deal_coupon'] = intval(0);
            }
            ######################################################
            ############ Get Restaurant Account detail ##########
            $restaurantAccount = new \Restaurant\Model\RestaurantAccounts ();
            $isRegisterRestaurant = $restaurantAccount->getRestaurantAccountDetail(array(
                'columns' => array(
                    'restaurant_id'
                ),
                'where' => array(
                    'restaurant_id' => $userOrderData['restaurant_id'],
                    'status' => 1
                )
            ));
            #####################################################
            ########## Know tip Type ############################
            $userOrderData['tiptype'] = ($userOrderData['tip_percent'] > 0) ? "p" : "c";
            #####################################################
            ################## is restaurant register or not ####
            if (isset($isRegisterRestaurant['restaurant_id']) && !empty($isRegisterRestaurant['restaurant_id'])) {
                $userOrderData['restaurant']['is_register'] = "1";
            } else {
                $userOrderData['restaurant']['is_register'] = "0";
            }
            #####################################################
            ########## Check is preorder enabled or not #########
            if (isset($userOrderData['restaurant']['is_reservation']) && $userOrderData['restaurant'] ['is_register'] != '' && $userOrderData['restaurant']['is_accept_cc'] != '' && $userOrderData['restaurant']['menu_available'] && ($userOrderData['restaurant']['menu_without_price'] == 0)) {
                $userOrderData['restaurant']['preordering_enabled'] = intval(1);
            } else {
                $userOrderData['restaurant']['preordering_enabled'] = intval(0);
            }
            ######################################################
            ########### check is delivery ########################
            $currentDayDelivery = StaticOptions::getPerDayDeliveryStatus($userOrderData['restaurant_id']);
//            if ($restaurantData[0]['is_delivery'] == 1) {
//                $userOrderData['restaurant']['is_delevery'] = ($currentDayDelivery) ? "1" : "0";
//            } else {
                $userOrderData['restaurant']['is_delevery'] = $restaurantData[0]['is_delivery'];
 //           }
            ######################################################

            $userOrderData['restaurant']['name'] = $userOrderData['restaurant_name'];
            $userOrderData['restaurant']['rest_code'] = strtolower($userOrderData['rest_code']);
            $userOrderData['restaurant']['delivery_charge'] = $restaurantData[0]['delivery_charge'];
            $userOrderData['restaurant']['tax_percentage'] = $restaurantData[0]['sales_tax'];
            $userOrderData['restaurant']['delivery_area'] = $restaurantData[0]['delivery_area'];
            $userOrderData['restaurant']['is_reservation'] = $restaurantData[0]['reservations'];
            $userOrderData['restaurant']['is_dining'] = $restaurantData[0]['dining'];
            $userOrderData['restaurant']['is_accept_cc'] = $restaurantData[0]['accept_cc_phone'];
            $userOrderData['restaurant']['minimum_delivery_amount'] = $restaurantData[0]['minimum_delivery_amount'];
            $userOrderData['restaurant']['is_takeout'] = $restaurantData[0]['takeout'];
            $userOrderData['restaurant']['menu_available'] = $restaurantData[0]['menu_available'];
            $userOrderData['restaurant']['menu_without_price'] = $restaurantData[0]['menu_without_price'];
            $userOrderData['restaurant']['order_pass_through'] = $restaurantData[0]['order_pass_through'];
            $userOrderData['latitude'] = $restaurantData[0]['res_latitude'];
            $userOrderData['longitude'] = $restaurantData[0]['res_longitude'];
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
                $userOrderDetailModel = new UserOrderDetail();
                $joins_items = array();
                $joins_items [] = array(
                    'name' => array(
                        'm' => 'menus'
                    ),
                    'on' => new \Zend\Db\Sql\Expression("user_order_details.item_id=m.id and m.status = 1 and m.online_order_allowed = 1"),
                    'columns' => array(
                        'item_status' => 'status',
                        'item_image_url' => 'image_name',
                        'item_desc',
                        'online_order_allowed',
                    ),
                    'type' => 'inner'
                );
                $joins_items [] = array(
                    'name' => array(
                        'mp' => 'menu_prices'
                    ),
                    'on' => new \Zend\Db\Sql\Expression("user_order_details.item_id=mp.menu_id and user_order_details.item_price_id=mp.id"),
                    'columns' => array(
                        'item_price_id' => 'id',
                        'unit_price' => new \Zend\Db\Sql\Expression('IF(mp.price IS NULL,0,round(mp.price,2))')                       
                    ),
                    'type' => 'inner'
                );
                
//                $joins_items[] = array(                        
//                    'name' => array(
//                        'ma' => 'menu_addons'
//                    ),
//                    'on' => new \Zend\Db\Sql\Expression(
//                            "ma.menu_id=user_order_details.item_id and ma.menu_price_id=user_order_details.item_price_id"),
//                    'columns' => array(
//                        'price_description',
//                        'menu_price_id'
//                    ),
//                    'type' => 'inner'    
//                );
                
                
//                $joins_items[] = array(                        
//                    'name' => array(
//                        'uoa' => 'user_order_addons'
//                    ),
//                    'on' => new \Zend\Db\Sql\Expression("user_order_details.id=uoa.user_order_detail_id"),
//                    'columns' => array(
//                        'menu_addons_option_id',
//                        'menu_addons_id',
//                        'addons_option',
//                        'price',
//                        'quantity',
//                        'priority',
//                        'was_free',
//                        'selection_type'
//                    ),
//                    'type' => 'inner'    
//                );
                $userOrderItem = $userOrderDetailModel->getAllOrderDetail(array(
                    'columns' => array(
                        'order_item_id' => 'id',
                        'item_id',
                        'item_name' => 'item',
                        'item_qty' => 'quantity',
                        'unit_price',
                        'item_special_instruction' => 'special_instruction',
                        'item_price_desc'
                    ),
                    'where' => array(
                        'user_order_details.user_order_id' => $id,
                    ),
                    'joins' => $joins_items
                ));

               //pr($userOrderItem,1);

                $userOrderData['item_list'] = $userOrderItem;
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
                
                $newSubTotal = 0;
//               pr($userOrderData,1);
                $addonSettingsObj = new \Restaurant\Model\MenuAddonsSetting();
                $menuAddonsObj = new \Restaurant\Model\MenuAddons();
                $optionsArray = [];
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
                        'type' => 'inner'
                    );
                foreach ($userOrderData['item_list'] as $key1 => $value) {
                      
                    $orderItemId = $userOrderData['item_list'][$i]['order_item_id'];
                    $userOrderData['item_list'][$i]['category_items']['online_order_allowed'] = $userOrderData['item_list'][$i]['online_order_allowed'];
                    $userOrderData['item_list'][$i]['category_items']['item_id'] = $userOrderData['item_list'][$i]['item_id'];
                    $userOrderData['item_list'][$i]['category_items']['item_image_url'] = $userOrderData['item_list'][$i]['item_image_url'];
                    $userOrderData['item_list'][$i]['category_items']['item_name'] = html_entity_decode(htmlspecialchars_decode($userOrderData['item_list'][$i]['item_name'], ENT_QUOTES));
                    $userOrderData['item_list'][$i]['category_items']['item_special_instruction']= html_entity_decode(htmlspecialchars_decode($userOrderData['item_list'][$i]['item_special_instruction'], ENT_QUOTES));
                    $userOrderData['item_list'][$i]['category_items']['item_desc'] = $userOrderData['item_list'][$i]['item_desc'];
                    $userOrderData['item_list'][$i]['category_items']['item_qty'] = $userOrderData['item_list'][$i]['item_qty'];
                    if ($userOrderData['item_list'][$i]['item_status'] == 1) {
                        $prices['status'] = true;
                    } else {
                        $prices['status'] = false;
                    }
                    
                    $prices['id'] = $userOrderData['item_list'][$i]['item_price_id'];
                    $prices['desc'] = $userOrderData['item_list'][$i]['item_price_desc'];
                    //$prices['id'] = $userOrderData['item_list'][$i]['item_price_id'];
                    $prices['value'] = $userOrderData['item_list'][$i]['unit_price'];
                    $prices['desc'] = "";
                    $userOrderData['item_list'][$i]['category_items']['prices'][] = $prices;
                    $newSubTotal = $newSubTotal+($value['item_qty']*$value['unit_price']);
                    
                    
                    $addon = $userOrderAddonsModel->getAllOrderAddon(array(
                        'columns' => array('menu_addons_id','menu_addons_option_id',
                            'addons_option','price','quantity','priority','was_free'
                        ),
                        'where' => array('user_order_detail_id' => $orderItemId),
                       'joins'=>$joins_addons
                    ));
                    
                    //pr($addon,1);
                    if(!empty($addon)){
                         $y = 0;
                         $k = 0;
                         $z = 0;
                         
                        foreach ($addon as $key => $result) {
                            if($result['addon_status']==1){
                                $addonSettings = $addonSettingsObj->reorderAddonSetting($result['menu_addons_id']);
                                $userOrderData['item_list'][$i]['data'][$k]['menu_price_id']=$userOrderData['item_list'][$i]['item_price_id'];
                                $userOrderData['item_list'][$i]['data'][$k]['addons'][$z]['addon_id']=$result['menu_addons_id'];
                                $userOrderData['item_list'][$i]['data'][$k]['addons'][$z]['selection_type']=$result['selection_type'];
                                $userOrderData['item_list'][$i]['data'][$k]['addons'][$z]['item_limit']=$addonSettings['item_limit'];
                                $userOrderData['item_list'][$i]['data'][$k]['addons'][$z]['enable_pricing_beyond']=$addonSettings['enable_pricing_beyond'];
                                $userOrderData['item_list'][$i]['data'][$k]['addons'][$z]['options'][] = $result;
                                $z++;
                            }
                        }  
                    }else{
                        $userOrderData['item_list'][$i]['data'] = array();
                    }
                    
//                  $addons = $userOrderModel->menuAddons($userOrderData['item_list'][$i]['item_id'],$userOrderData['item_list'][$i]['item_price_id'], $addonsId,$selectionType);
                    $userOrderData['item_list'][$i]['item_name'] = $userFunctions->to_utf8($userOrderData['item_list'][$i]['item_name']);
                    $i ++;
                }
              // pr($userOrderData,1);
                $orderSubTotal = 0;
                $orderSubTotal = (float) $newSubTotal;//$userOrderData['order_amount'];
                $totalOrderPrice = 0;
                $orderTax = (float) $userOrderData['tax'];
                if (is_numeric($orderTax) & $orderTax != 0) {
                    $userOrderData['tax'] = number_format($orderTax, 2);
                    $totalOrderPrice = $orderSubTotal + $orderTax;
                }
                $orderTip = (float) $userOrderData['tip_amount'];
                if (is_numeric($orderTip) & $orderTip != 0) {
                    $userOrderData['tip_amount'] = number_format($orderTip, 2);
                    $totalOrderPrice = $totalOrderPrice + $orderTip;
                }
                $orderDelCharge = (float) $userOrderData['delivery_charge'];
                if (is_numeric($orderDelCharge) & $orderDelCharge != 0) {
                    $userOrderData['delivery_charge'] = number_format($orderDelCharge, 2);
                    $totalOrderPrice = $totalOrderPrice + $orderDelCharge;
                }
                $orderDiscount = (float) $userOrderData['deal_discount'];
                if (is_numeric($orderDiscount) & $orderDiscount != 0) {
                    $userOrderData['deal_discount'] = number_format($orderDiscount, 2);
                    $totalOrderPrice = $totalOrderPrice - $orderDiscount;
                }
                //pr($userOrderData,1);
                $userOrderData['order_amount_calculation']['subtotal'] = (string)number_format($orderSubTotal,2);
                $userOrderData['order_amount_calculation']['total_order_price'] = number_format($totalOrderPrice, 2, '.', ',');
                $userOrderData['total_amount'] = number_format($totalOrderPrice, 2, '.', ',');
                unset($userOrderData['name_on_card'], $userOrderData['card_number'], $userOrderData['card_type'], $expired_on[1], $expired_on[0], $userOrderData['billing_zip'], $userOrderData['delivery_charge'], $userOrderData['tax'], $userOrderData['tip_amount'], $userOrderData['delivery_address'], $userOrderData['deal_discount'], $userOrderData['deal_title'], $userOrderData['order_amount'], $userOrderData['expired_on'], $userOrderData['user_comments'], $userOrderData['city'], $userOrderData['street'], $userOrderData['state_code'], $userOrderData['zipcode'], $userOrderData['apt_suite'], $userOrderData['phone'], $userOrderData['promocode_discount']);
                $this->addTags($userOrderData);
                return $userOrderData;
            }
        }else{
             throw new \Exception('Order details not found');
        }
    }
    private function addTags(&$userOrderData){
        $tags = new \Home\Model\RestaurantTag();
        $userOrderData['restaurant']['tags_fct'] = $tags->getTags($userOrderData['restaurant_id']);
    }

}
