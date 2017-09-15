<?php

namespace Ariahk\Controller;

use MCommons\Controller\AbstractRestfulController;
use Restaurant\OrderFunctions;
use MStripe;
use User\Model\UserOrderDetail;
use User\Model\UserOrderAddons;
use User\Model\UserOrder;
use User\Model\DbTable\UserOrderTable;
use MCommons\StaticOptions;
use User\UserFunctions;
use User\Model\User;
use Home\Model\City;
use MUtility\MunchException;
use Restaurant\Model\Restaurant;
use Ariahk\AriaFunctions;
use User\Model\UserNotification;
use Restaurant\Model\RestaurantAccounts;

class AriaOrderController extends AbstractRestfulController {

    public function create($data) {
        $userModel = new User();
        $userOrder = new UserOrder ();
        $userFunctions = new UserFunctions ();
        $orderFunctions = new OrderFunctions ();
        $ariaFunction = new AriaFunctions ();
        $restaurantModel = new Restaurant ();
        $restaurantAccount = new RestaurantAccounts ();
        $userNotificationModel = new UserNotification ();
        $config = $this->getServiceLocator()->get('Config');
        $userId = $this->getUserSession()->getUserId();
        if (empty($data ['order_details'] ['restaurant_id']) && !isset($data ['order_details'] ['restaurant_id'])) {
            throw new \Exception('Restaurant is not valid');
        }
        $userAddressData = array();
        $userAddressData ['latitude'] = isset($data ['user_details']['address_lat']) ? $data ['user_details']['address_lat'] : 0;
        $userAddressData ['longitude'] = isset($data ['user_details']['address_lng']) ? $data ['user_details']['address_lng'] : 0;

        ########### Getting Current Time ##################
        $session = $this->getUserSession();
        $selectedLocation = $session->getUserDetail('selected_location', array());
        $cityId = $selectedLocation ['city_id'];
        $cityModel = new City ();
        $cityDetails = $cityModel->cityDetails($cityId);
        $currentCityDateTime = StaticOptions::getRelativeCityDateTime(array(
                    'state_code' => $cityDetails [0] ['state_code']
        ));
        $currentDateTimeUnixTimeStamp = $currentCityDateTime->getTimestamp();
        $dbtable = new UserOrderTable ();
        $dbtable->getWriteGateway()->getAdapter()->getDriver()->getConnection()->beginTransaction();
        try {
            $user_address_id = "";
            $user_instruction = "";
            $orderPass = $orderFunctions->getOrderPassThrough($data ['order_details'] ['restaurant_id']);
            $registerRestaurant = true;

            $dealDetails = array();
            $userPromocodesDetails = array();
            $finalPrice = $orderFunctions->calculatePrice($data ['order_details'], $dealDetails, $userPromocodesDetails);
            if ($finalPrice >= APPLIED_FINAL_TOTAL) {
                $cardDetails = $data ['card_details'];

                if (empty($cardDetails ['card_no'])) {
                    throw new \Exception('Card details not sent');
                }
                if (empty($cardDetails ['expiry_month'])) {
                    throw new \Exception('Expiry month not sent');
                }
                if (empty($cardDetails ['billing_zip'])) {
                    throw new \Exception('Billing zip not sent');
                }
                if (empty($cardDetails ['expiry_year'])) {
                    throw new \Exception('Expiry year not sent');
                }
                if (empty($cardDetails ['name_on_card'])) {
                    throw new \Exception('Name on card not sent');
                }
                if (empty($cardDetails ['cvc'])) {
                    throw new \Exception('CVC not sent');
                }
                $cardDetails = array(
                    'number' => $cardDetails ['card_no'],
                    'exp_month' => $cardDetails ['expiry_month'],
                    'exp_year' => $cardDetails ['expiry_year'],
                    'name' => $cardDetails ['name_on_card'],
                    'cvc' => $cardDetails ['cvc'],
                    'address_zip' => $cardDetails['billing_zip'],
                );
                // charge user
                if (isset($orderPass) && $orderPass == 0) {// order_pass_through if 1 then not sent to stripe to charge   
                    $stripeModel = new MStripe($this->getStripeKey());
                    $selectedLocation = $this->getUserSession()->getUserDetail('selected_location');
                    $stripeResponse = $stripeModel->chargeCard($cardDetails, $finalPrice);
                }

                if (isset($stripeResponse ['status']) && !(int) $stripeResponse ['status'] && $orderPass == 0) {
                    $message = "We could not charge your credit card, please ensure all fields are entered correctly.";
                    throw new MunchException($stripeResponse['message'], $message);
                }

                if ($orderPass == 0) {
                    if (isset($data ['card_details']['stripe_token_id']) && !empty($data ['card_details']['stripe_token_id'])) {
                        $userOrder->card_number = $data ['card_details']['card_number'];
                    } else {
                        $userOrder->card_number = substr($cardDetails ['number'], - 4);
                    }
                } else {
                    if (isset($data ['card_details']['stripe_token_id']) && !empty($data ['card_details']['stripe_token_id'])) {
                        $userCard = new \User\Model\UserCard();
                        $ccDetail = $userCard->getUserDecriptCard($userId, $data ['card_details']['id']);
                        $cc = $orderFunctions->aesDecrypt($ccDetail[0]['encrypt_card_number']);
                        $ccArray = explode("-", $cc);
                        $expMY = explode("/", $ccDetail[0]['expired_on']);
                        $cardDetails = array(
                            'card_no' => $ccArray [0],
                            'expiry_month' => $expMY [0],
                            'expiry_year' => $expMY [1],
                            'name_on_card' => $ccDetail[0] ['name_on_card'],
                            'cvc' => $ccArray [1],
                            'billing_zip' => $ccDetail[0]['zipcode'],
                        );
                        $userOrder->encrypt_card_number = $orderFunctions->aesEncrypt($cardDetails ['card_no'] . "-" . $cardDetails ['cvc']);
                        $userOrder->card_number = substr($cardDetails ['card_no'], - 4);
                    } else {
                        $userOrder->encrypt_card_number = $orderFunctions->aesEncrypt($cardDetails ['number'] . "-" . $cardDetails ['cvc']);
                        $userOrder->card_number = substr($cardDetails ['number'], - 4);
                    }
                }

                $userOrder->name_on_card = $data ['card_details'] ['name_on_card'];
                $userOrder->card_type = isset($data ['card_details'] ['card_type']) ? $data ['card_details'] ['card_type'] : 'cc';
                $userOrder->expired_on = $data ['card_details'] ['expiry_month'] . '/' . $data ['card_details'] ['expiry_year'];
                $userOrder->stripe_charge_id = isset($stripeResponse['id']) ? $stripeResponse['id'] : '';
            } else {
                $userOrder->card_number = '';
                $userOrder->name_on_card = '';
                $userOrder->card_type = '';
                $userOrder->expired_on = '';
                $userOrder->stripe_charge_id = '';
            }//end of final price condition

            $currentTime = StaticOptions::getRelativeCityDateTime(array(
                        'restaurant_id' => $data ['order_details'] ['restaurant_id']
                    ))->format(StaticOptions::MYSQL_DATE_FORMAT);
            $extendTime = true;

            $actualDeliveryDate = $data ['order_details'] ['delivery_date'];
            $actualDeliveryTime = $data ['order_details'] ['delivery_time'];
            $deliveryDateTime = $data ['order_details'] ['delivery_date'] . ' ' . $data ['order_details'] ['delivery_time'] . ':00';

            $differenceOfTimeInMin = (int) ((strtotime($deliveryDateTime) - strtotime($currentTime)) / 60);

            if (ucwords($data ['order_details'] ['order_type']) == "Takeout") {
                $orderFinal ['timeslots'] = array();
                foreach ($ariaFunction->getAriaTakeoutTimeSlots($data ['order_details'] ['restaurant_id'], $data ['order_details'] ['delivery_date']) as $t) {
                    if ($t ['status'] == 1) {
                        $orderFinal ['timeslots'] [] = $t ['slot'];
                    }
                }

                $totalSlots = count($orderFinal ['timeslots']);
                $lastSlotOfTekeout = $orderFinal ['timeslots'][$totalSlots - 1];

                if (strtotime($data ['order_details'] ['delivery_date'] . " " . $lastSlotOfTekeout) == strtotime($data ['order_details'] ['delivery_date'] . " " . $data ['order_details'] ['delivery_time'])) {
                    $deliveryDateTime = date('Y-m-d H:i:s', strtotime($data ['order_details'] ['delivery_date'] . " " . $lastSlotOfTekeout));
                    $extendTime = false;
                }
            }
            if ($differenceOfTimeInMin < 45 && $extendTime) {
                $deliveryTimestamp = strtotime($currentTime) + 45 * 60;
                $actualDeliveryDate = date('Y-m-d', $deliveryTimestamp);
                $actualDeliveryTime = date('H:i', $deliveryTimestamp);
                $deliveryDateTime = date('Y-m-d H:i:s', $deliveryTimestamp);
            }

            $dateOfOrder = StaticOptions::getFormattedDateTime($currentTime, 'Y-m-d H:i:s', 'D, M j, Y');
            $timeOfOrder = StaticOptions::getFormattedDateTime($currentTime, 'Y-m-d H:i:s', 'h:i A');
            $dateTimeOfOrder = $dateOfOrder . ' at ' . $timeOfOrder;
            $dateOfDelivery = StaticOptions::getFormattedDateTime($deliveryDateTime, 'Y-m-d H:i:s', 'D, M j, Y');
            $timeOfDelivery = StaticOptions::getFormattedDateTime($deliveryDateTime, 'Y-m-d H:i:s', 'h:i A');
            $dateTimeOfDelivery = $dateOfDelivery . ' at ' . $timeOfDelivery;
            //$userOrder->status = $orderFunctions->getOrderStatus($actualDeliveryDate, $actualDeliveryTime, $data ['order_details'] ['restaurant_id']);
            $userOrder->status = "ordered";
            $ipAddress = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
            $userDetailAddress = isset($data ['user_details'] ['address']) ? $data ['user_details'] ['address'] : "";
            $user_instruction = isset($data ['order_details'] ['own_instruction']) ? $data ['order_details'] ['own_instruction'] : "";
            $userOrder->order_pass_through = $orderPass;
            $userOrder->order_amount = $orderFunctions->subtotal;
            $userOrder->total_amount = $orderFunctions->finalTotal;
            $userOrder->tax = $orderFunctions->tax;
            $userOrder->delivery_charge = $orderFunctions->deliveryCharge;
            $userOrder->tip_amount = $orderFunctions->tipAmount;
            $userOrder->tip_percent = $orderFunctions->tipPercent;
            $userOrder->restaurant_id = $data ['order_details'] ['restaurant_id'];
            $userOrder->fname = $data ['user_details'] ['fname'];
            $userOrder->lname = $data ['user_details'] ['lname'];
            $userOrder->email = $data ['user_details'] ['email'];
            $userOrder->city = isset($data ['user_details'] ['city']) ? $data ['user_details'] ['city'] : '';
            $userOrder->city_id = $cityId;
            $userOrder->apt_suite = isset($data ['user_details'] ['apt_suit']) ? $data ['user_details'] ['apt_suit'] : '';
            $userOrder->delivery_address = isset($data ['order_details'] ['delivery_address']) ? $data ['order_details'] ['delivery_address'] : "";
            $userOrder->address = $userDetailAddress;
            $userOrder->miles_away = isset($restaurantDistance ['res_distance']) ? $restaurantDistance ['res_distance'] : 0;
            $userOrder->phone = isset($data ['user_details'] ['phone']) ? $data ['user_details'] ['phone'] : "";
            $userOrder->state_code = isset($data ['user_details'] ['state_code']) ? $data ['user_details'] ['state_code'] : "";
            $userOrder->zipcode = isset($data ['user_details'] ['zipcode']) ? $data ['user_details'] ['zipcode'] : "";
            $userOrder->billing_zip = $data ['card_details'] ['billing_zip'];
            $userOrder->order_type = ucwords($data ['order_details'] ['order_type']);
            $userOrder->delivery_time = $deliveryDateTime;
            $userOrder->order_type1 = $data ['order_details'] ['order_type1'];
            $userOrder->order_type2 = $data ['order_details'] ['order_type2'];
            $userOrder->special_checks = implode('||', $data ['order_details'] ['special_instruction']);
            $userOrder->new_order = 0;
            $userOrder->host_name = (isset($data['host_name']) && !empty($data['host_name'])) ? $data['host_name'] : 'aria';
            $userOrder->user_comments = isset($data ['order_details']['user_comments']) ? $data ['order_details']['user_comments'] : '';
            $userOrder->payment_receipt = $orderFunctions->generateReservationReceipt();
            $userOrder->created_at = StaticOptions::getRelativeCityDateTime(array(
                        'restaurant_id' => $data ['order_details'] ['restaurant_id']
                    ))->format(StaticOptions::MYSQL_DATE_FORMAT);
            $userOrder->updated_at = StaticOptions::getRelativeCityDateTime(array(
                        'restaurant_id' => $data ['order_details'] ['restaurant_id']
                    ))->format(StaticOptions::MYSQL_DATE_FORMAT);
            $userOrder->promocode_discount = 0;
            $userOrder->deal_id = 0;
            $userOrder->deal_title = "";
            $userOrder->user_ip = $ipAddress;
            $userOrder->latitude = $userAddressData['latitude'];
            $userOrder->longitude = $userAddressData['longitude'];
            $userOrder->pay_via_point = 0;
            $userOrder->pay_via_card = 0.00;
            $userOrder->redeem_point = 0;
            $userOrderId = $userOrder->addtoUserOrder();
            $userOrderId = $userOrderId ['id'];
            $restshortkey = $data['order_details']['restshortkey'];
            $userOrderDetails = new UserOrderDetail ();
            $userOrderAddons = new UserOrderAddons ();
            foreach ($orderFunctions->itemDetails as $item) {
                $userOrderDetails->user_order_id = $userOrderId;
                $userOrderDetails->item = $item ['item_name'];
                $userOrderDetails->item_description = $item ['item_desc'];
                $userOrderDetails->item_id = $item ['item_id'];
                $userOrderDetails->quantity = $item ['quantity'];
                $userOrderDetails->item_price_id = $item ['price_id'];
                $userOrderDetails->unit_price = $item ['unit_price'];
                $userOrderDetails->total_item_amt = $item ['total_item_amount'];
                $userOrderDetails->item_price_desc = $item ['price_desc'];
                $userOrderDetails->special_instruction = $item ['special_instruction'];
                $userOrderDetails->status = 1;

                $userOrderDetailId = $userOrderDetails->addtoUserOrderDetail();
                if (!empty($item ['addons'])) {
                    foreach ($item ['addons'] as $addon) {
                        $userOrderAddons->user_order_detail_id = $userOrderDetailId;
                        $userOrderAddons->user_order_id = $userOrderId;
                        $userOrderAddons->addons_option = $addon ['addon_option'];
                        $userOrderAddons->menu_addons_id = $addon ['addon_id'];
                        $userOrderAddons->menu_addons_option_id = $addon ['addon_option_id'];
                        $userOrderAddons->price = $addon ['price'];
                        $userOrderAddons->quantity = $item ['quantity'];
                        $userOrderAddons->selection_type = 1;
                        $userOrderAddons->priority = $addon['priority'];
                        $userOrderAddons->was_free = $addon['was_free'];
                        $userOrderAddons->addtoUserOrderAddons();
                    }
                }
            }

            $webUrl = PROTOCOL . $config ['constants'] ['web_url'];
            $address = "";
            if (isset($data ['order_details'] ['delivery_address']) && isset($data ['user_details'] ['city']) && isset($userOrder->state_code) && isset($userOrder->zipcode)) {
                $address = $userOrder->delivery_address . ', ' . $userOrder->city . ', ' . $userOrder->state_code . ', ' . $userOrder->zipcode;
            }

            $data = array(
                'name' => $data ['user_details'] ['fname'],
                'hostName' => $webUrl,
                'restaurantName' => $orderFunctions->restaurantName,
                'orderType' => ucwords($data ['order_details'] ['order_type']) ,
                'receiptNo' => $userOrder->payment_receipt,
                'timeOfOrder' => $dateTimeOfOrder,
                'timeOfDelivery' => $dateTimeOfDelivery,
                'orderData' => $ariaFunction->makeAriaOrderForMail($orderFunctions->itemDetails, $userOrder->restaurant_id, $userOrder->status, $orderFunctions->subtotal),
                'subtotal' => $orderFunctions->subtotal,
                'discount' => 0,
                'tax' => $orderFunctions->tax,
                'tipAmount' => $userOrder->tip_amount,
                'total' => $orderFunctions->finalTotal,
                'cardType' => $userOrder->card_type,
                'cardNo' => $userOrder->card_number,
                'expiredOn' => $userOrder->expired_on,
                'email' => $userOrder->email,
                'specialInstructions' => $userOrder->special_checks,
                'type' => ucwords($data ['order_details'] ['order_type']),
                'address' => $address,
                'onlyDate' => $dateOfDelivery,
                'onlyTime' => $timeOfDelivery,
                'deliveryCharge' => $orderFunctions->deliveryCharge,
                'city' => isset($data ['user_details'] ['city']) ? $data ['user_details'] ['city'] : "",
                'state' => isset($data ['user_details'] ['state_code']) ? $data ['user_details'] ['state_code'] : "",
                'phone' => isset($data ['user_details'] ['phone']) ? $data ['user_details'] ['phone'] : "",
                'zip' => isset($data ['user_details'] ['zipcode']) ? $data ['user_details'] ['zipcode'] : "",
                'orderTime' => $timeOfOrder,
                'orderDate' => $dateOfOrder,
                'dealDiscount' => 0,
                'promocodeDiscount' => 0,
                'order_pass_through' => $orderPass,
            );
            $dbtable->getWriteGateway()->getAdapter()->getDriver()->getConnection()->commit();
            $currentTimeOrder = new \DateTime ();
            $arrivedTimeOrder = \DateTime::createFromFormat(StaticOptions::MYSQL_DATE_FORMAT, $userOrder->delivery_time);
            $currentTimeNewOrder = StaticOptions::getRelativeCityDateTime(array(
                        'restaurant_id' => $userOrder->restaurant_id
                    ))->format(StaticOptions::MYSQL_DATE_FORMAT);
            $differenceOfTimeInMin = round(abs(strtotime($arrivedTimeOrder->format("Y-m-d H:i:s")) - strtotime($currentTimeNewOrder)) / 60);
            $options = array("where" => array('id' => $userOrder->restaurant_id));
            $restaurantDetail = $restaurantModel->findRestaurant($options);
            $manager_phone = ($restaurantDetail->phone_no2)? $restaurantDetail ->phone_no2:'';
            $restaurantAccountDetails = $restaurantAccount->getRestaurantAccountDetail(array('where' => array(
                'restaurant_id' => $userOrder->restaurant_id,
                'status' => '1'
            )
            ));
            $manager_email = ($restaurantAccountDetails['memail'])? $restaurantAccountDetails['memail']:'';
            //send sms to User
           $ariaFunction->orderSmsAlert($data['phone']);
            //send sms to Aria Managers
           //$ariaFunction->ariaStaffSmsAlert($restshortkey,$manager_phone);
           //get footer data like image, alt etc
            $footerData = $ariaFunction->ariaFooterData($restshortkey);
           //send pubnub alert to ARIA
            $notificationMsg = "You have a new order. Way to go!";
            $channel = "dashboard_" . $userOrder->restaurant_id;
            $notificationArray = array(
                "msg" => $notificationMsg,
                "channel" => $channel,
                "type" => 'order',
                "userId" => '999999',
                "restaurantId" => $userOrder->restaurant_id,
                'curDate' => StaticOptions::getRelativeCityDateTime(array(
                    'restaurant_id' => $userOrder->restaurant_id
                ))->format(StaticOptions::MYSQL_DATE_FORMAT),
                'restaurant_name' => ucfirst($orderFunctions->restaurantName),
                'order_id' => $userOrderId
            );
            $notificationJsonArray = array('user_id' => '999999','order_id' => $userOrderId, 'restaurant_id' => $userOrder->restaurant_id, 'restaurant_name' => ucfirst($orderFunctions->restaurantName));
            $response = $userNotificationModel->createPubNubNotification($notificationArray, $notificationJsonArray);
            $pubnub = StaticOptions::pubnubPushNotification($notificationArray);
            $subject ="";
            $template ="";
           if($data['orderType']=='Takeout'){
                $template = 'email_manager_order';
                $subject = sprintf("You’ve Got a New %s Takeout Order!", $orderFunctions->restaurantName);
            }else{
                $template = 'email_manager_delivery';
                $subject = sprintf("You’ve Got a New %s Delivery Order!", $orderFunctions->restaurantName);
            }
           $sendAlertMailToManagerArray = array(
                    'variables' => array(
                        'ownername' => ucfirst($data['name']),
                        'restshrotkey'=>$restshortkey,
                        'restaurant_name' => $orderFunctions->restaurantName,
                        'facebook_url' => $restaurantDetail->facebook_url,
                        'instagram_url' => $restaurantDetail->instagram_url,
                        'orderDate' => $dateOfOrder,
                        'ordertime' => $timeOfOrder,
                        'receipt_no' => $userOrder->payment_receipt,
                        'timeOfOrder' => $data['timeOfOrder'],
                        'timeOfDelivery' => $data['timeOfDelivery'],
                        'specialInstructions' => $data['specialInstructions'],
                        'orderData' => $data['orderData'],
                        'subtotal' => $data['subtotal'],
                        'tax' => $data['tax'],
                        'tipAmount' => $data['tipAmount'],
                        'total' => $data['total'],
                        'cardType' => $data['cardType'],
                        'cardNo' => $data['cardNo'],
                        'expiredOn' => $data['expiredOn'],
                        'ordertype' => ucwords($data['orderType']),
                        'address' => $data['address'],
                        'deliveryCharge' => $data['deliveryCharge'],
                        'manager_email' => $manager_email,
                        'image1' =>$footerData['image_one'],
                        'alt1' =>$footerData['alt_one'],
                        'image2' =>$footerData['image_two'],
                        'alt2' =>$footerData['alt_two'],
                    ),
            'template' => $template,
            'subject' => $subject,
                );
             $ariaFunction->sendMailsToRestaurant($sendAlertMailToManagerArray); 
             return array(
                'id' => $userOrderId,
                'receipt' => $userOrder->payment_receipt,
                'order_status' => $userOrder->status,
            );
        } catch (MunchException $e) {
            $dbtable->getWriteGateway()->getAdapter()->getDriver()->getConnection()->rollback();
            $options = array(
                'cart_data' => json_encode($data),
                'exception' => $e->getMessage(),
                'origin' => 'File:' . $e->getFile() . '|Line:' . $e->getLine()
            );
            \MUtility\MunchLogger::writeAbandonedCartToDb($options);
            throw new \Exception($e->getCustomMessage(), 400);
        }
    }

}
