<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;
use Restaurant\OrderFunctions;
use MStripe;
use User\Model\UserOrderDetail;
use User\Model\UserOrderAddons;
use User\Model\UserOrder;
use User\Model\DbTable\UserOrderTable;
use MCommons\StaticOptions;
use User\UserFunctions;
use User\Model\UserNotification;
use User\Model\User;
use User\Model\UserAddress;
use User\Model\UserReservation;
use Home\Model\City;
use Restaurant\Model\UserPromocodes;
use Restaurant\Model\RestaurantAccounts;
use MUtility\MunchException;
use User\Model\UserReferrals;

class WebOrderController extends AbstractRestfulController {

    public function create($data) {
        $serverData = $this->getRequest()->getServer()->toArray();   //$serverData['HTTP_HOST']; //munch-local.com     
        $userModel = new User();
        $userOrder = new UserOrder ();
        $userFunctions = new UserFunctions ();
        $orderFunctions = new OrderFunctions ();
        $userreferral = new UserReferrals();
        $config = $this->getServiceLocator()->get('Config');
        $userId = $this->getUserSession()->getUserId();
        $notificationMsg = "";
        //$data ['user_details']['redeem_point'] = 510;
        if (empty($data ['order_details'] ['restaurant_id']) && !isset($data ['order_details'] ['restaurant_id'])) {
            throw new \Exception('Restaurant is not valid');
        }

        if (isset($data['order_details']['items']) && empty($data['order_details']['items']) && count($data['order_details']['items']) < 1) {
            throw new \Exception('Sorry we could not process your order as some of the items you selected are no longer offered by the restaurant.');
        }

        if (isset($data ['card_details']['save_card']) && $data ['card_details']['save_card'] == 1) {
            $saveCard = true;
        } else {
            $saveCard = false;
        }
        $cod = isset($data['cod']) ? (int) $data['cod'] : 0;
        ############## Order Pass Through ##################
        $orderPass = $orderFunctions->getOrderPassThrough($data ['order_details'] ['restaurant_id']);
        ####################################################

        if (isset($data["is_preorder_reservation"]) && $data["is_preorder_reservation"] == true) {
            $data['do_transaction'] = false;
            $data["is_preorder_reservation"] = false;
            return $this->savePreOrderReservation($data, $userId, $orderPass);
        }

        $userAddressData = array();
        $userAddressData ['latitude'] = (isset($data ['user_details']['address_lat']) && !empty($data ['user_details']['address_lat'])) ? trim($data ['user_details']['address_lat']) : 0;
        $userAddressData ['longitude'] = (isset($data ['user_details']['address_lng']) && !empty($data ['user_details']['address_lng'])) ? trim($data ['user_details']['address_lng']) : 0;
        $isPreOrderReservation = false;
        if (isset($data['do_transaction']) && $data['do_transaction'] == false) {
            $isPreOrderReservation = true;
        }

        ########### Getting Current Time ##################
        $session = $this->getUserSession();
        $selectedLocation = $session->getUserDetail('selected_location', array());

        if (isset($selectedLocation ['city_id'])) {
            $cityId = $selectedLocation ['city_id'];
            $cityModel = new City ();
            $cityDetails = $cityModel->cityDetails($cityId);
            $currentCityDateTime = StaticOptions::getRelativeCityDateTime(array(
                        'state_code' => $cityDetails [0] ['state_code']
            ));

            $currentDateTimeUnixTimeStamp = strtotime($currentCityDateTime->format('Y-m-d H:i:s'));
        } elseif (isset($data ['order_details'] ['restaurant_id']) && !empty($data ['order_details'] ['restaurant_id'])) {
            $currentDateTime = StaticOptions::getRelativeCityDateTime(array(
                        'restaurant_id' => $data ['order_details'] ['restaurant_id']
                    ))->format("Y-m-d h:i");
            $currentDateTimeUnixTimeStamp = strtotime($currentDateTime);
        } else {
            $currentCityDateTime = StaticOptions::getRelativeCityDateTime(array(
                        'state_code' => "NY"
            ));

            $currentDateTimeUnixTimeStamp = strtotime($currentCityDateTime->format('Y-m-d H:i:s'));
        }

        ###################################################  
        /*
         * // Tested for this data $data ['order_details'] = array ( 'restaurant_id' => 2966, 'delivery_address' => 'gurgaon sector 44', 'delivery_date' => '2012-12-01', 'delivery_time' => '22:11', 'special_instruction' => array ( 'Make it fast' ), 'order_type' => 'takeout', 'order_type1' => 'I', 'order_type2' => 'P', 'discount' => '10', 'discount_type' => '%', 'tip_percent' => '', 'tax' => '', 'items' => array ( array ( 'id' => 0, 'item_id' => 269, 'price_id' => 968, 'quantity' => 1, 'special_instruction' => 'less salt', 'addons' => '299,300' ), array ( 'id' => 1, 'item_id' => 277, 'price_id' => 968, 'quantity' => 2, 'special_instruction' => 'excessive salt', 'addons' => '303' ) ) ); $data ['card_details'] = array ( 'card_id' => '8', "card_no" => "4242424242424242", "expiry_month" => "06", "expiry_year" => "2014", "name_on_card" => "Birju Shah", "cvc" => 121, "billing_zip" => "1A121", 'card_type' => 'cc' ); $data ['user_details'] = array ( "fname" => "First Name", "lname" => "Last Name", "email" => "birju112@gmail.com", "city" => "San Francisco", "apt_suit" => "131", "address" => "Sector 44, Near CNG Petrol Pump", "phone" => "998887778", "state_code" => "CA", 'save_card' => 0 );
         */

        $dbtable = new UserOrderTable ();
        if (!$isPreOrderReservation) {
            $dbtable->getWriteGateway()->getAdapter()->getDriver()->getConnection()->beginTransaction();
        } else {
            $reserved_seats = $data["reservation_details"]['reserved_seats'];
        }

        try {
            $user_address_id = "";

            #################bp_status##########
            $bpoption = array('columns' => array('bp_status', 'phone', 'first_name', 'last_name'), 'where' => array('id' => $userId));
            $bp_status = $userModel->getUser($bpoption);
            //$locationModel = new RestaurantDistanceCalculationFunction();
            $user_instruction = "";
            $restaurantAccount = new RestaurantAccounts ();
            $isRegisterRestaurant = $restaurantAccount->getRestaurantAccountDetail(array(
                'columns' => array(
                    'restaurant_id'
                ),
                'where' => array(
                    'restaurant_id' => $data ['order_details'] ['restaurant_id'],
                    'status' => 1
                )
            ));

            if (isset($isRegisterRestaurant['restaurant_id']) && !empty($isRegisterRestaurant['restaurant_id'])) {
                $registerRestaurant = true;
            } else {
                $registerRestaurant = false;
            }


            ############Deal Data############
            $dealDetails = array();
            if (isset($data['deal_id']) && !empty($data['deal_id'])) {
                $dealDetails = $orderFunctions->restaurantDeal($data['deal_id'], $registerRestaurant, $currentDateTimeUnixTimeStamp);
            }
            #################################
            ### User Promocode Detail ###
            $userPromocodesDetails = [];
            $hostName = "";
            if (isset($data['host_name']) && $data['host_name'] != PROTOCOL . SITE_URL) {
                $hostName = $data['host_name'];
                $restaurantId = $data ['order_details'] ['restaurant_id'];
                $promocode = (isset($data['promocode']) && !empty($data['promocode'])) ? $data['promocode'] : false;
                if ($promocode) {
                    //$options = array("promocode"=>$promocode,'restaurant_id'=>$restaurantId);
                    $userPromocodesDetails = $userFunctions->getPromocode($promocode, $restaurantId);
                    $userPromocodesDetails['discount_coupon'] = 1;
                }
            } else {
                $userFunctions->userId = $userId;
                $userFunctions->getUserPromocodeDetails();
                $userFunctions->currentDateTimeUnixTimeStamp = $currentDateTimeUnixTimeStamp;
                if ($userFunctions->userPromocodes) {
                    if ($userFunctions->getNewUserPromotion()) {
                        //if ($registerRestaurant) {
                        $userFunctions->userPromocodes[$userFunctions->promocodeId]['promocodeType'] = (int) 1;
                        $userFunctions->userPromocodes[$userFunctions->promocodeId]['cityDateTime'] = $currentCityDateTime->format('Y-m-d H:i:s');
                        $userPromocodesDetails = $userFunctions->userPromocodes[$userFunctions->promocodeId];
                        $userPromocodesDetails['discount_coupon'] = 0;
                        //}
                    } elseif ($userFunctions->getUserPromocode()) {
                        $userFunctions->userPromocodes[$userFunctions->promocodeId]['promocodeType'] = (int) 0;
                        $userFunctions->userPromocodes[$userFunctions->promocodeId]['cityDateTime'] = $currentCityDateTime->format('Y-m-d H:i:s');
                        $userPromocodesDetails = $userFunctions->userPromocodes[$userFunctions->promocodeId];
                        $userPromocodesDetails['discount_coupon'] = 0;
                    }
                }
            }

            ##################################
            $orderFunctions->orderPass = $orderPass;
            #################### Check Exist User Address ######################
            if (isset($data['user_address_id']) && !empty($data['user_address_id']) && $userId) {
                $user_address_id = $data['user_address_id'];
                $options = array(
                    'where' => array(
                        'id' => $user_address_id
                    )
                );

                $addressModel = new UserAddress();
                $userAddressDetailExist = $addressModel->getUserAddressInfo($options);
                if ($userAddressDetailExist) {
                    $firstNameLastName = explode(" ", $userAddressDetailExist['address_name']);
                    $data ['user_details'] ['fname'] = isset($bp_status['first_name']) ? $bp_status['first_name'] : '';
                    $data ['user_details'] ['lname'] = isset($bp_status['last_name']) ? $bp_status['last_name'] : '';
                    $data ['user_details'] ['email'] = $userAddressDetailExist['email'];
                    $data ['user_details'] ['city'] = $userAddressDetailExist['city'];
                    $data ['user_details'] ['apt_suit'] = $userAddressDetailExist['apt_suite'];
                    $data ['user_details'] ['phone'] = $userAddressDetailExist['phone'];
                    $data ['user_details'] ['state_code'] = $userAddressDetailExist['state'];
                    $data ['user_details'] ['address'] = $userAddressDetailExist['street'];
                    $data ['user_details'] ['zipcode'] = $userAddressDetailExist['zipcode'];
                    if (!empty($userAddressDetailExist['apt_suite'])) {
                        $data ['order_details'] ['delivery_address'] = $userAddressDetailExist['street'] . ", " . $userAddressDetailExist['apt_suite'];
                    } else {
                        $data ['order_details'] ['delivery_address'] = $userAddressDetailExist['street'];
                    }
                    $data['user_details']['address_lat'] = $userAddressDetailExist['latitude'];
                    $data['user_details']['address_lng'] = $userAddressDetailExist['longitude'];
                    $userAddressData ['latitude'] = $userAddressDetailExist['latitude'];
                    $userAddressData ['longitude'] = $userAddressDetailExist['longitude'];
                }
            }
            ############################################################
            ######################Redeem point##########################
            $usrTotalPoint = $userFunctions->userTotalPoint($userId);
            $orderFunctions->restaurant_id = $data ['order_details'] ['restaurant_id'];
            if (isset($data ['user_details']['redeem_point']) && !empty($data ['user_details']['redeem_point']) && $data ['user_details']['redeem_point'] > 0 && $usrTotalPoint >= POINT_REDEEM_LIMIT && $orderPass == 0) {
                $orderFunctions->point = $data ['user_details']['redeem_point'];
            }

            ############################################################
            ################### Calculate final price ################## 
            $finalPrice = $orderFunctions->calculatePrice($data ['order_details'], $dealDetails, $userPromocodesDetails);
            ############################################################    

            if ($finalPrice >= APPLIED_FINAL_TOTAL && $cod == 0) {
                if (isset($bp_status['bp_status']) && $bp_status['bp_status'] == 1) {

                    $data ['card_details'] ['card_number'] = '4242';
                    $data ['card_details'] ['expiry_month'] = '1';
                    $data ['card_details'] ['billing_zip'] = '12345';
                    $data ['card_details'] ['expiry_year'] = '20';
                    $data ['card_details'] ['name_on_card'] = 'demo';
                    $data ['card_details'] ['cvc'] = '123';
                    $cardDetails = $data ['card_details'];
                    $cardDetails = array(
                        'number' => $cardDetails ['card_number'],
                        'exp_month' => $cardDetails ['expiry_month'],
                        'exp_year' => $cardDetails ['expiry_year'],
                        'name' => $cardDetails ['name_on_card'],
                        'cvc' => $cardDetails ['cvc'],
                        'address_zip' => $cardDetails['billing_zip'],
                    );
                } elseif (isset($data ['card_details']['id']) && $orderPass == 1) {
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
                } else {
                    $cardDetails = $data ['card_details'];
                }
                if (empty($bp_status['bp_status']) || $bp_status['bp_status'] == NULL || $bp_status['bp_status'] == 0) {
                    if (!isset($cardDetails ['stripe_token_id']) || empty($cardDetails ['stripe_token_id'])) {

                        if (!isset($cardDetails ['card_no']) && !empty($cardDetails ['card_no'])) {
                            throw new \Exception('Card details not sent');
                        }
                        if (!isset($cardDetails ['expiry_month']) && !empty($cardDetails ['expiry_month'])) {
                            throw new \Exception('Expiry month not sent');
                        }
                        if (!isset($cardDetails ['billing_zip']) && !empty($cardDetails ['billing_zip'])) {
                            throw new \Exception('Billing zip not sent');
                        }
                        if (!isset($cardDetails ['expiry_year']) && !empty($cardDetails ['expiry_year'])) {
                            throw new \Exception('Expiry year not sent');
                        }
                        if (!isset($cardDetails ['name_on_card']) && !empty($cardDetails ['name_on_card'])) {
                            throw new \Exception('Name on card not sent');
                        }
                        if (!isset($cardDetails ['cvc']) && !empty($cardDetails ['cvc'])) {
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
                    } else {
                        $cardDetails = $cardDetails ['stripe_token_id'];
                    }

                    // charge user
                    if (isset($orderPass) && $orderPass == 0) {// order_pass_through if 1 then not sent to stripe to charge 
                        $stripeModel = new MStripe($this->getStripeKey());
                        $selectedLocation = $this->getUserSession()->getUserDetail('selected_location');
                        $stripeResponse = $stripeModel->chargeCard($cardDetails, $finalPrice, $orderFunctions->restaurantName);
                    }

                    if (isset($stripeResponse ['status']) && !(int) $stripeResponse ['status'] && $orderPass == 0) {
                        $message = "We could not charge your credit card, please ensure all fields are entered correctly.";
                        throw new MunchException($stripeResponse['message'], $message);
                    }

                    //validate card

                    if ($orderPass == 1) {
                        try {
                            $userFunctions->validateCardFromStripe($cardDetails);
                        } catch (\Exception $e) {
                            throw new \Exception("We could not charge your credit card, please ensure all fields are entered correctly.", 400);
                        }
                    }

                    // Save card

                    if ($saveCard) {
                        try {
                            $userFunctions->saveCardToStripeAndDatabase($cardDetails, $saveCard, $orderPass);
                        } catch (\Exception $e) {
                            throw new \Exception("We could not charge your credit card, please ensure all fields are entered correctly.", 400);
                        }
                    }
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

            if ($userId) {
                $userOrder->user_id = $userId;
            }

            ################################
            /* Logic for extend delivery time  time difference is less than 45 min */
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
                foreach (StaticOptions::getRestaurantTakeoutTimeSlots($data ['order_details'] ['restaurant_id'], $data ['order_details'] ['delivery_date']) as $t) {
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
            $userOrder->status = $orderFunctions->getOrderStatus($actualDeliveryDate, $actualDeliveryTime, $data ['order_details'] ['restaurant_id']);
            ################################
            ############ Get user IP Address ##############           
            $ipAddress = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
            ###############################################
            $userDetailAddress = isset($data ['user_details'] ['address']) ? trim($data ['user_details'] ['address']) : "";
            $user_instruction = isset($data ['order_details'] ['own_instruction']) ? $data ['order_details'] ['own_instruction'] : "";
            $userOrder->order_pass_through = $orderPass;
            $userOrder->order_amount = $orderFunctions->subtotal;
            if ($orderFunctions->discountAmountOnPoint > 0) {
                $userOrder->total_amount = $orderFunctions->finalTotal + $orderFunctions->discountAmountOnPoint;
            } else {
                $userOrder->total_amount = $orderFunctions->finalTotal;
            }
            $userOrder->cod = $cod;
            $userOrder->deal_discount = $orderFunctions->dealDiscount;
            $userOrder->tax = $orderFunctions->tax;
            $userOrder->delivery_charge = $orderFunctions->deliveryCharge;
            $userOrder->tip_amount = $orderFunctions->tipAmount;
            $userOrder->tip_percent = $orderFunctions->tipPercent;
            $userOrder->restaurant_id = $data ['order_details'] ['restaurant_id'];
            $userOrder->fname = $data ['user_details'] ['fname'];
            $userOrder->lname = $data ['user_details'] ['lname'];
            $userOrder->email = $data ['user_details'] ['email'];
            $userOrder->city = isset($data ['user_details'] ['city']) ? trim($data ['user_details'] ['city']) : '';
            $userOrder->city_id = $cityId;
            $userOrder->apt_suite = isset($data ['user_details'] ['apt_suit']) ? trim($data ['user_details'] ['apt_suit']) : '';
            $userOrder->delivery_address = isset($data ['order_details'] ['delivery_address']) ? trim($data ['order_details'] ['delivery_address']) : "";
            $userOrder->address = $userDetailAddress;
            $userOrder->miles_away = isset($restaurantDistance ['res_distance']) ? $restaurantDistance ['res_distance'] : 0;
            $userOrder->phone = isset($data ['user_details'] ['phone']) ? trim($data ['user_details'] ['phone']) : "";
            $userOrder->state_code = isset($data ['user_details'] ['state_code']) ? trim($data ['user_details'] ['state_code']) : "";
            $userOrder->zipcode = isset($data ['user_details'] ['zipcode']) ? trim($data ['user_details'] ['zipcode']) : "";
            $userOrder->billing_zip = $data ['card_details'] ['billing_zip'];
            $userOrder->order_type = isset($data ['order_details'] ['order_type'])?ucwords($data ['order_details'] ['order_type']):"";
            $userOrder->delivery_time = $deliveryDateTime;
            $userOrder->order_type1 = isset($data ['order_details'] ['order_type1'])?$data ['order_details'] ['order_type1']:"";
            $userOrder->order_type2 = isset($data ['order_details'] ['order_type2'])?$data ['order_details'] ['order_type2']:"";
            $userOrder->special_checks = implode('||', $data ['order_details'] ['special_instruction']);
            $userOrder->new_order = 0;
            $userOrder->host_name = (isset($data['host_name']) && !empty($data['host_name'])) ? $data['host_name'] : PROTOCOL . SITE_URL; //munch-local.com
            $userOrder->user_comments = isset($data ['order_details']['user_comments']) ? $data ['order_details']['user_comments'] : '';
            $userOrder->payment_receipt = $orderFunctions->generateReservationReceipt();
            $userOrder->created_at = StaticOptions::getRelativeCityDateTime(array(
                        'restaurant_id' => $data ['order_details'] ['restaurant_id']
                    ))->format(StaticOptions::MYSQL_DATE_FORMAT);
            $userOrder->updated_at = StaticOptions::getRelativeCityDateTime(array(
                        'restaurant_id' => $data ['order_details'] ['restaurant_id']
                    ))->format(StaticOptions::MYSQL_DATE_FORMAT);
            $userOrder->promocode_discount = $orderFunctions->promocodeDiscount;
            $userOrder->pay_via_point = $orderFunctions->discountAmountOnPoint;
            $userOrder->pay_via_card = ($orderFunctions->finalTotal > APPLIED_FINAL_TOTAL && $cod == 0) ? $orderFunctions->finalTotal : 0;
            $userOrder->redeem_point = $orderFunctions->point;
            $userOrder->deal_id = $orderFunctions->deal_id;
            $userOrder->deal_title = $orderFunctions->deal_title;
            $userOrder->user_ip = $ipAddress;
            $userOrder->latitude = $userAddressData['latitude'];
            $userOrder->longitude = $userAddressData['longitude'];
            $userOrderId = $userOrder->addtoUserOrder();
            $userOrderId = $userOrderId ['id'];
            ###########update user phone number if phone number is empty, null or not exist ##########
            if ((empty($bp_status['phone']) || $bp_status['phone'] == null) && $userId) {
                $userModel->id = $userId;
                $phoneData = array('phone' => $userOrder->phone);
                $userModel->update($phoneData);
            }
            ##########################################################################################
            ########### Register User & Dine-More ####################
            $dm_register = false;
            //$data['user_details']['dm_register'] =1;
            if (!$userId && isset($data['user_details']['dm_register'])) {
                $loyalityCode = substr($orderFunctions->restaurantName, 0, 1) . $data ['order_details'] ['restaurant_id'] . "00";
                $userDataDuringOrder = array(
                    'first_name' => $data ['user_details'] ['fname'],
                    'last_name' => $data ['user_details'] ['lname'],
                    'email' => $data ['user_details'] ['email'],
                    'phone' => $data ['user_details'] ['phone'],
                    'cityid' => $cityId,
                    'user_source' => 'ws',
                    'loyality_code' => $loyalityCode,
                    'current_date' => $userOrder->created_at,
                    'restaurant_name' => $orderFunctions->restaurantName,
                    'restaurant_id' => $data ['order_details'] ['restaurant_id']
                );

                $dm_register = $userFunctions->dmUserRegisterDuringOrder($userDataDuringOrder);
            }

            ##### Update user procode redeam status ####

            if (!isset($data['host_name']) && !empty($userPromocodesDetails)) {
                if ($orderFunctions->priceForPromocodeCal != 0) {
                    $userPromocodeModel = new UserPromocodes();
                    $userPromocodeModel->id = $userPromocodesDetails['user_promocode_id'];
                    $userPromocodeData = array('order_id' => $userOrderId, 'reedemed' => 1);
                    $userPromocodeModel->update($userPromocodeData);
                }
            }

            if ($orderFunctions->promocodeType == 3 && $orderFunctions->promocodeId > 0) {
                $promocodesModel = new \Restaurant\Model\Promocodes();
                $data = array('budget' => $orderFunctions->balenceBudget);
                $promocodesModel->id = $orderFunctions->promocodeId;
                $promocodesModel->update($data);
            }
            ############################################
            ############ Transaction amount against point redeem ###########
            if ($orderFunctions->discountAmountOnPoint > 0) {
                $orderFunctions->transaction('credit', 'credit amount against redeemed point during order $' . $orderFunctions->discountAmountOnPoint . ' order id : ' . $userOrderId);
                $orderFunctions->transaction("debit", "debit amount against order $" . $orderFunctions->discountAmountOnPoint . " order id : " . $userOrderId);
                $orderFunctions->updateUserPoint($userOrderId);
            }

            ################################################################


            $foodBookmark = new \Bookmark\Model\FoodBookmark();
            $userOrderDetails = new UserOrderDetail ();
            $userOrderAddons = new UserOrderAddons ();
            $itemDescription = "";
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

                if (isset($item['deal_id']) && !empty($item['deal_id'])) {
                    $itemDealDetails = $orderFunctions->restaurantDeal($item['deal_id'], $registerRestaurant, $currentDateTimeUnixTimeStamp);
                    $orderFunctions->deal_id = $itemDealDetails['id'];
                    if ($itemDealDetails['user_deals'] == 1 && $itemDealDetails['deal_used_type'] == 1) {
                        $orderFunctions->dealAvailedByUser();
                    }
                }
                ############### Bookmark Menu ##################
                if ($userId) {

                    ########## Check existing bookmark #############
                    $foodBookmark->getDbTable()->setArrayObjectPrototype('ArrayObject');
                    $options = array('columns' => array('menu_id', 'id'),
                        'where' => array(
                            'menu_id' => $item ['item_id'],
                            'user_id' => $userId,
                            'type' => 'ti'
                        )
                    );
                    $isAlreadyBookedmark = $foodBookmark->find($options)->toArray();
                    ################################################

                    if (empty($isAlreadyBookedmark)) {
                        $foodBookmark->user_id = $userId;
                        $foodBookmark->menu_id = $item ['item_id'];
                        $foodBookmark->restaurant_id = $userOrder->restaurant_id;
                        $foodBookmark->type = 'ti';
                        $foodBookmark->menu_name = $item ['item_name'];
                        $foodBookmark->created_on = StaticOptions::getRelativeCityDateTime(array(
                                    'restaurant_id' => $userOrder->restaurant_id
                                ))->format(StaticOptions::MYSQL_DATE_FORMAT);

                        $foodBookmark->addBookmark();
                    }
                }
                ############# End of bookmark ###################
                $itemDescription .=$item ['item_name'] . ", ";
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
                'name' => isset($data ['user_details'] ['fname']) ? $data ['user_details'] ['fname'] : "",
                'hostName' => $webUrl,
                'restaurantName' => $orderFunctions->restaurantName,
                'orderType' => (isset($userOrder->order_type1) && $userOrder->order_type1 == 'I') ? 'Individual ' . ucwords($userOrder->order_type) : 'Group ' . ucwords($userOrder->order_type),
                'receiptNo' => $userOrder->payment_receipt,
                'timeOfOrder' => $dateTimeOfOrder,
                'timeOfDelivery' => $dateTimeOfDelivery,
                'orderData' => $orderFunctions->makeOrderForMail($orderFunctions->itemDetails, $userOrder->restaurant_id, $userOrder->status, $orderFunctions->subtotal),
                'subtotal' => $orderFunctions->subtotal,
                'discount' => $orderFunctions->dealDiscount,
                'tax' => $orderFunctions->tax,
                'tipAmount' => $orderFunctions->tipAmount,
                'total' => $orderFunctions->finalTotal,
                'cardType' => $userOrder->card_type,
                'cardNo' => $userOrder->card_number,
                'expiredOn' => $userOrder->expired_on,
                'email' => $userOrder->email,
                'specialInstructions' => $userOrder->special_checks,
                'type' => isset($userOrder->order_type) ? ucwords($userOrder->order_type) : "",
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
                'dealDiscount' => $orderFunctions->dealDiscount,
                'promocodeDiscount' => $userOrder->promocode_discount,
                'order_pass_through' => $orderPass,
                'redeemedPointAmt' => $orderFunctions->discountAmountOnPoint,
            );
            // create auto restaurant bookmark
            if ($userId) {

                /*
                 * Update user instruction
                 */

                if (!empty($user_instruction)) {
                    $userModel->id = $userId;

                    if ($userOrder->order_type === "Takeout") {
                        $userModel->update(array('takeout_instructions' => $user_instruction));
                    } elseif ($userOrder->order_type === "Delivery") {
                        $userModel->update(array('delivery_instructions' => $user_instruction));
                    }
                }
            }

            #####  Send mail to Dashboard "wecare@munchado.com" #####
            $userFunctions->sendOrderMail($data, $userOrder->status, $userId, $userOrder->restaurant_id);
            #####  End Send mail to CRM "wecare@munchado.com" #####
            $dbtable->getWriteGateway()->getAdapter()->getDriver()->getConnection()->commit();

            /**
             * Push To Pubnub For User
             */
            $currentTimeOrder = new \DateTime ();
            $arrivedTimeOrder = \DateTime::createFromFormat(StaticOptions::MYSQL_DATE_FORMAT, $userOrder->delivery_time);
            $currentTimeNewOrder = StaticOptions::getRelativeCityDateTime(array(
                        'restaurant_id' => $userOrder->restaurant_id
                    ))->format(StaticOptions::MYSQL_DATE_FORMAT);
            $differenceOfTimeInMin = round(abs(strtotime($arrivedTimeOrder->format("Y-m-d H:i:s")) - strtotime($currentTimeNewOrder)) / 60);
            //$orderFunctions->sendSmsforOrder($data, $userOrder->status, $userId, $userOrder->restaurant_id, $userOrder->delivery_time);
//                $userNotificationModel = new UserNotification ();
//                if ($userOrder->status == 'placed') {
//                    if ($differenceOfTimeInMin <= 90) {
//
//                         if ((isset($orderPass) && $orderPass == 1) && $userOrder->order_type === "Takeout") {
//                            $notificationMsg = 'We got your order and we’re hungry just thinking about it...';
//                        }else if ((isset($orderPass) && $orderPass == 1) && $userOrder->order_type === "Delivery") {
//                            $notificationMsg = 'We got your order and we’re hungry just looking at it...';
//                        } else if (isset($orderPass) && $orderPass == 1) {
//                            $notificationMsg = 'We got your pre-order for today and we’re hungry just thinking about it.';
//                        } else if($registerRestaurant==true && $userOrder->order_type === "Takeout"){
//                            $notificationMsg = "We got your order and we’re hungry just thinking about it...";
//                        } else if($registerRestaurant==true && $userOrder->order_type === "Delivery"){
//                            $notificationMsg = "We got your order and we’re hungry just looking at it...";
//                        } else {
//                            $notificationMsg = 'We got your pre-order and we’re a little jelly you’re the one eating it, not us.';
//                        }
//                        $channel = "mymunchado_" . $userId;
//                        $notificationArray = array(
//                            "msg" => $notificationMsg,
//                            "channel" => $channel,
//                            "userId" => $userId,
//                            "type" => 'order',
//                            "restaurantId" => $userOrder->restaurant_id,
//                            'curDate' => StaticOptions::getRelativeCityDateTime(array(
//                                'restaurant_id' => $userOrder->restaurant_id
//                            ))->format(StaticOptions::MYSQL_DATE_FORMAT),
//                            'restaurant_name' => ucfirst($orderFunctions->restaurantName),
//                            'order_id' => $userOrderId,
//                            'is_live' => 1
//                        );
//                        $notificationJsonArray = array('user_id' => $userId, 'order_id' => $userOrderId, 'restaurant_id' => $userOrder->restaurant_id, 'restaurant_name' => ucfirst($orderFunctions->restaurantName));
//                        $response = $userNotificationModel->createPubNubNotification($notificationArray, $notificationJsonArray);
//                        $pubnub = StaticOptions::pubnubPushNotification($notificationArray);
//                    } elseif(strtotime($arrivedTimeOrder->format("Y-m-d H:i:s")) > strtotime($currentTimeNewOrder)){
//                       if($registerRestaurant==true && $userOrder->order_type === "Delivery"){
//                            $notificationMsg = 'We saw your pre-order for today and we’re a little jelly you’re the one eating it, not us.';
//                        } else if ((isset($orderPass) && $orderPass == 1) && $userOrder->order_type === "Delivery") {
//                            $notificationMsg = "We got your pre-order for today and we’re hungry just thinking about it...";
//                        }else if ((isset($orderPass) && $orderPass == 1) && $userOrder->order_type === "Takeout") {
//                            $notificationMsg = "We got your pre-order for today and we’re hungry just thinking about it...";
//                        } else if($registerRestaurant==true && $userOrder->order_type === "Takeout"){
//                            $notificationMsg = "We saw your pre-order for today and we’re a little jelly you’re the one eating it, not us.";                            
//                        }
//                        
//                        if(strtotime($arrivedTimeOrder->format("Y-m-d")) > strtotime(date('Y-m-d', strtotime($currentTimeNewOrder)))){
//                            if($registerRestaurant==true && $userOrder->order_type === "Takeout"){
//                            $notificationMsg = "We got your pre-order and we’re a little jelly you’re the one eating it, not us.";                            
//                            }
//                            else if ((isset($orderPass) && $orderPass == 1) && $userOrder->order_type === "Takeout") {
//                            $notificationMsg = "We got your pre-order and we’re hungry just thinking about it...";
//                            } else if($registerRestaurant==true && $userOrder->order_type === "Delivery"){
//                            $notificationMsg = 'We got your pre-order and we’re a little jelly you’re the one eating it, not us.';
//                        } else if ((isset($orderPass) && $orderPass == 1) && $userOrder->order_type === "Delivery") {
//                            $notificationMsg = "We got your pre-order and we’re hungry just thinking about it...";
//                        }else{
//                            $notificationMsg = "We got your pre-order and we’re hungry just thinking about it...";
//                        }
//                        }
//                        $channel = "mymunchado_" . $userId;
//                        $notificationArray = array(
//                            "msg" => $notificationMsg,
//                            "channel" => $channel,
//                            "userId" => $userId,
//                            "type" => 'order',
//                            "restaurantId" => $userOrder->restaurant_id,
//                            'curDate' => StaticOptions::getRelativeCityDateTime(array(
//                                'restaurant_id' => $userOrder->restaurant_id
//                            ))->format(StaticOptions::MYSQL_DATE_FORMAT),
//                            'restaurant_name' => $orderFunctions->restaurantName,
//                            'order_id' => $userOrderId,
//                            'is_live' => 1
//                        );
//                        $notificationJsonArray = array('user_id' => $userId, 'order_id' => $userOrderId, 'restaurant_id' => $userOrder->restaurant_id, 'restaurant_name' => $orderFunctions->restaurantName);
//                        $response = $userNotificationModel->createPubNubNotification($notificationArray, $notificationJsonArray);
//                        $pubnub = StaticOptions::pubnubPushNotification($notificationArray);
//                    } else {
//                        if ((isset($orderPass) && $orderPass == 1) && $userOrder->order_type === "Takeout") {
//                            $notificationMsg = "We got your pre-order for today and we're hungry just thinking about it...";
//                        } else if ((isset($orderPass) && $orderPass == 1) && $userOrder->order_type === "Delivery") {
//                            $notificationMsg = "We got your order and we’re hungry just looking at it...";
//                        } else if ($registerRestaurant == true && $userOrder->order_type === "Delivery") {
//                            $notificationMsg = 'We saw your pre-order for today and we’re a little jelly you’re the one eating it, not us.';
//                        } else if ($registerRestaurant == true && $userOrder->order_type === "Takeout") {
//                            $notificationMsg = 'We saw your pre-order for today and we’re a little jelly you’re the one eating it, not us.';
//                        } else if ($registerRestaurant == true && $userOrder->order_type === "Takeout" && (isset($orderPass) && $orderPass == 1)) {
//                            $notificationMsg = 'We got your pre-order for today and we’re hungry just thinking about it...';
//                        } else if ($registerRestaurant == true) {
//                            $notificationMsg = 'We got your order and we’re hungry just looking at it...';
//                        } else {
//                            $notificationMsg = "We saw your pre-order for today and we’re a little jelly you’re the one eating it, not us.";
//                        }
//                        $channel = "mymunchado_" . $userId;
//                        $notificationArray = array(
//                            "msg" => $notificationMsg,
//                            "channel" => $channel,
//                            "userId" => $userId,
//                            "type" => 'order',
//                            "restaurantId" => $userOrder->restaurant_id,
//                            'curDate' => StaticOptions::getRelativeCityDateTime(array(
//                                'restaurant_id' => $userOrder->restaurant_id
//                            ))->format(StaticOptions::MYSQL_DATE_FORMAT),
//                            'restaurant_name' => $orderFunctions->restaurantName,
//                            'order_id' => $userOrderId,
//                            'is_live' => 1
//                        );
//                        $notificationJsonArray = array('user_id' => $userId, 'order_id' => $userOrderId, 'restaurant_id' => $userOrder->restaurant_id, 'restaurant_name' => $orderFunctions->restaurantName);
//                        $response = $userNotificationModel->createPubNubNotification($notificationArray, $notificationJsonArray);
//                        $pubnub = StaticOptions::pubnubPushNotification($notificationArray);
//                    }
//                }
//                if ($userOrder->status == 'ordered') {
//
//                    ########################### Push pubnub for user ###########
//                         if ((isset($orderPass) && $orderPass == 1) && $userOrder->order_type === "Takeout") {
//                            $notificationMsgToUser = 'We got your order and we’re hungry just thinking about it...';
//                        } else if (isset($orderPass) && $orderPass == 1) {
//                            $notificationMsgToUser = 'We got your order and we’re hungry just looking at it...';
//                        } else if($registerRestaurant==true && $userOrder->order_type === "Takeout"){
//                            $notificationMsgToUser = "We got your order and we’re hungry just thinking about it...";
//                        } else {
//                            $notificationMsgToUser = 'We got your order and we’re hungry just looking at it...';
//                        }
//                        
//                    $channelToUser = "mymunchado_" . $userId;
//                    $notificationArrayToUser = array(
//                        "msg" => $notificationMsgToUser,
//                        "channel" => $channelToUser,
//                        "userId" => $userId,
//                        "type" => 'order',
//                        "restaurantId" => $userOrder->restaurant_id,
//                        'curDate' => StaticOptions::getRelativeCityDateTime(array(
//                            'restaurant_id' => $userOrder->restaurant_id
//                        ))->format(StaticOptions::MYSQL_DATE_FORMAT),
//                        'restaurant_name' => ucfirst($orderFunctions->restaurantName),
//                        'order_id' => $userOrderId,
//                        'is_live' => 1
//                    );
//                    $notificationJsonArrayToUser = array('user_id' => $userId, 'order_id' => $userOrderId, 'restaurant_id' => $userOrder->restaurant_id, 'restaurant_name' => ucfirst($orderFunctions->restaurantName));
//                    $response = $userNotificationModel->createPubNubNotification($notificationArrayToUser, $notificationJsonArrayToUser);
//                    $pubnub = StaticOptions::pubnubPushNotification($notificationArrayToUser);
//
//
//                    ########################### Push pubnub for Restaurant ###################
//                      if(isset($orderPass) && $orderPass == 0){
//                    if($userOrder->order_type === "Takeout"){
//                    $notificationMsg = "You have a new takeout order. (".$userOrder->payment_receipt.") Way to go!";    
//                    }else{
//                    $notificationMsg = "You have a new delivery order. Receipt number: ".$userOrder->payment_receipt.". Way to go!";
//                    }
//                    $channel = "dashboard_" . $userOrder->restaurant_id;
//                    $notificationArray = array(
//                        "msg" => $notificationMsg,
//                        "channel" => $channel,
//                        "userId" => $userId,
//                        "type" => 'order',
//                        "restaurantId" => $userOrder->restaurant_id,
//                        'curDate' => StaticOptions::getRelativeCityDateTime(array(
//                            'restaurant_id' => $userOrder->restaurant_id
//                        ))->format(StaticOptions::MYSQL_DATE_FORMAT),
//                        'restaurant_name' => ucfirst($orderFunctions->restaurantName),
//                        'order_id' => $userOrderId
//                    );
//                    $notificationJsonArray = array('user_id' => $userId, 'order_id' => $userOrderId, 'restaurant_id' => $userOrder->restaurant_id, 'restaurant_name' => ucfirst($orderFunctions->restaurantName));
//                    $response = $userNotificationModel->createPubNubNotification($notificationArray, $notificationJsonArray);
//                    $pubnub = StaticOptions::pubnubPushNotification($notificationArray);
//                }
//                }
            /*
             * Push pubnub for cms Dashboard
             */

            if ($userOrder->order_type === "Takeout") {
//                    $notificationMsg = "You have received a new takeout order.";
//                    $channel = "cmsdashboard";

                if (isset($data ['order_details'] ['own_instruction']) && !empty($data ['order_details'] ['own_instruction'])) {
                    $userModel->update(array('takeout_instructions' => $data ['order_details'] ['own_instruction']));
                }
            } elseif ($userOrder->order_type === "Delivery") {
//                    $notificationMsg = "You have received a new delivery order.";
//                    $channel = "cmsdashboard";
                if (isset($data ['order_details'] ['own_instruction']) && !empty($data ['order_details'] ['own_instruction'])) {
                    $userModel->update(array('delivery_instructions' => $data ['order_details'] ['own_instruction']));
                }
            }

//                $notificationArray = array(
//                    "msg" => $notificationMsg,
//                    "channel" => $channel,
//                    "userId" => $userId,
//                    "type" => 'order',
//                    "restaurantId" => $userOrder->restaurant_id,
//                    'curDate' => StaticOptions::getRelativeCityDateTime(array(
//                        'restaurant_id' => $userOrder->restaurant_id
//                    ))->format(StaticOptions::MYSQL_DATE_FORMAT),
//                    'restaurant_name' => ucfirst($orderFunctions->restaurantName),
//                    'order_id' => $userOrderId
//                );
//                $notificationJsonArray = array('user_id' => $userId, 'order_id' => $userOrderId, 'restaurant_id' => $userOrder->restaurant_id, 'restaurant_name' => ucfirst($orderFunctions->restaurantName));
//                $response = $userNotificationModel->createPubNubNotification($notificationArray, $notificationJsonArray);
//                $pubnub = StaticOptions::pubnubPushNotification($notificationArray);
            /*
             * End of Push pubnub for cms Dashboard
             */

            $userPoints = '';
            if ($userId) {
                $userPointsCount = new \User\Model\UserPoint();
                $userPointsSum = $userPointsCount->countUserPoints($userId);
                $redeemPoint = $userPointsSum[0]['redeemed_points'];
                $userPoints = strval($userPointsSum[0]['points'] - $redeemPoint);
            }

            if ($orderFunctions->discountAmountOnPoint > 0) {
                $restaurantFunctions = new \Restaurant\RestaurantDetailsFunctions();
                $restuarantAddress = $restaurantFunctions->restaurantAddress($userOrder->restaurant_id);

                ############## salesmanago redeem event ##################
                $salesData['email'] = $userOrder->email;
                $salesData['owner_email'] = 'no-reply@munchado.com';
                $salesData['identifier'] = "redeemed";
                $salesData['point'] = (int) $userOrder->redeem_point;
                $salesData['totalpoint'] = (int) $userPoints;
                //$userFunctions->createQueue($salesData, 'Salesmanago');
                $salesData['identifier'] = "event";
                $salesData['description'] = "redeem";
                $salesData['restaurant_name'] = $orderFunctions->restaurantName;
                $salesData['location'] = $restuarantAddress;
                $salesData['value'] = (int) $userOrder->redeem_point;
                $salesData['contact_ext_event_type'] = "OTHER";
                $salesData['restaurant_id'] = $userOrder->restaurant_id;
                $salesData['email'] = $userOrder->email;
                //$userFunctions->createQueue($salesData, 'Salesmanago');
                ###################################################
            }


            #######Add address information into  user_addresses table######
            if ($userId && empty($user_address_id) && $userOrder->order_type === "Delivery" && $userAddressData['latitude'] != 0 && $userAddressData['longitude'] != 0) {
                $userAddressData['user_id'] = $userId;
                $userAddressData['email'] = $userOrder->email;
                $userAddressData['apt_suite'] = $userOrder->apt_suite;
                $userAddressData['address_name'] = $userOrder->fname . " " . $userOrder->lname;
                $userAddressData['street'] = $userDetailAddress;
                $userAddressData['city'] = $userOrder->city;
                $userAddressData['state'] = $userOrder->state_code;
                $userAddressData['phone'] = $userOrder->phone;
                $userAddressData['zipcode'] = $userOrder->zipcode; //we are getting billing zip so no need to save in user_address table
                $userAddressData['address_type'] = "s"; //it is default value by requirment instruction
                $userAddressData ['status'] = 1;
                $userAddressData ['created_on'] = $currentTime;
                $userAddressData ['updated_at'] = $currentTime;
                $userAddressData['google_addrres_type'] = 'street';

                $options = array(
                    'columns' => array('id', 'street', 'apt_suite', 'status'),
                    'where' => array(
                        'user_id' => $userId,
                        'latitude' => $userAddressData['latitude'],
                        'longitude' => $userAddressData['longitude'],
                        'address_type' => 's'
                    )
                );
                $addressModel = new UserAddress();
                $userAddressDetail = $addressModel->getUserAddressInfo($options);
                if (empty($userAddressDetail)) {
                    $userFunctions->addUserAddress($userAddressData);
                } else {
                    if (isset($userAddressDetail['id'])) {
                        $addressModel->user_id = $userId;
                        $addressModel->address_name = $userOrder->fname . " " . $userOrder->lname;
                        $addressModel->apt_suite = $userOrder->apt_suite;
                        $addressModel->email = $userOrder->email;
                        $addressModel->street = $userDetailAddress;
                        $addressModel->city = $userOrder->city;
                        $addressModel->state = $userOrder->state_code;
                        $addressModel->phone = $userOrder->phone;
                        $addressModel->zipcode = $userOrder->zipcode;
                        $addressModel->status = 1;
                        $addressModel->updated_at = $currentTime;
                        $addressModel->created_on = $currentTime;
                        $addressModel->address_type = "s";
                        $addressModel->google_addrres_type = 'street';
                        $addressModel->latitude = $userAddressData ['latitude'];
                        $addressModel->longitude = $userAddressData ['longitude'];
                        $addressModel->id = $userAddressDetail['id'];
                        $addressModel->addAddress();
                    }
                }
            }
            ########End of add user address############
//            if ($isPreOrderReservation) {
//                $userNotificationModel = new UserNotification ();
//                $feedDate = date('M d Y', strtotime($dateOfDelivery));
//                $feedTime = date('h:i a', strtotime($timeOfDelivery));
//                $notificationMsgToUser = "We got your pre-paid reservation and will let you know once it’s confirmed.";
//                   $channelToUser = "mymunchado_" . $userId;
//                    $notificationArrayToUser = array(
//                        "msg" => $notificationMsgToUser,
//                        "channel" => $channelToUser,
//                        "userId" => $userId,
//                        "type" => 'reservation',
//                        "restaurantId" => $userOrder->restaurant_id,
//                        'curDate' => StaticOptions::getRelativeCityDateTime(array(
//                            'restaurant_id' => $userOrder->restaurant_id
//                        ))->format(StaticOptions::MYSQL_DATE_FORMAT),
//                        'restaurant_name' => ucfirst($orderFunctions->restaurantName),
//                        'reservation_time' => $feedTime,
//                        'reservation_date' => $feedDate,
//                        'no_of_people' => $reserved_seats
//                    );
//                    $notificationJsonArrayToUser = array('reservation_time' => $feedTime,'reservation_date' => $feedDate,'no_of_people' => $reserved_seats,'user_id' => $userId, 'order_id' => $userOrderId, 'restaurant_id' => $userOrder->restaurant_id, 'restaurant_name' => ucfirst($orderFunctions->restaurantName));
//                    $response = $userNotificationModel->createPubNubNotification($notificationArrayToUser, $notificationJsonArrayToUser);
//                    $pubnub = StaticOptions::pubnubPushNotification($notificationArrayToUser);
//                    
//                    $notificationMsg = "You have a new pre-paid reservation! Way to go!";
//                    $channel = "dashboard_" . $userOrder->restaurant_id;
//                    $notificationArray = array(
//                        "msg" => $notificationMsg,
//                        "channel" => $channel,
//                        "userId" => $userId,
//                        "type" => 'reservation',
//                        "restaurantId" => $userOrder->restaurant_id,
//                        'curDate' => StaticOptions::getRelativeCityDateTime(array(
//                            'restaurant_id' => $userOrder->restaurant_id
//                        ))->format(StaticOptions::MYSQL_DATE_FORMAT),
//                        'restaurant_name' => ucfirst($orderFunctions->restaurantName)
//                    );
//                    $notificationJsonArray = array('user_id' => $userId, 'order_id' => $userOrderId, 'restaurant_id' => $userOrder->restaurant_id, 'restaurant_name' => ucfirst($orderFunctions->restaurantName));
//                    $response = $userNotificationModel->createPubNubNotification($notificationArray, $notificationJsonArray);
//                    $pubnub = StaticOptions::pubnubPushNotification($notificationArray);
//                
//                
//                return array(
//                    'id' => $userOrderId,
//                    'receipt' => $userOrder->payment_receipt,
//                    'points' => (string) (int) $userPoints,
//                    'orderpoints' => (string) (int) $orderFunctions->user_order_point,
//                    'order_status' => $userOrder->status,
//                    'subTotal' => $orderFunctions->subtotal,
//                    'tax' => $orderFunctions->tax,
//                    'tipAmount' => $orderFunctions->tipAmount,
//                    'dealDiscount' => $orderFunctions->dealDiscount,
//                    'promocodeDiscount' => $orderFunctions->promocodeDiscount,
//                    'finalTotal' => $orderFunctions->finalTotal,
//                    'redeem_point' => ($orderFunctions->point == 0) ? "" : (string) $orderFunctions->point,
//                    'pay_via_point' => ($orderFunctions->discountAmountOnPoint == 0) ? "" : (string) $orderFunctions->discountAmountOnPoint,
//                    'pay_via_card' => ($userOrder->pay_via_card == 0 || $cod == 1) ? "" : (string) $userOrder->pay_via_card,
//                    'pay_via_cash' => ($cod == 1) ? (string) number_format($userOrder->total_amount-$orderFunctions->discountAmountOnPoint):'',
//                    'cod' => $cod
//                );
//            } else {
            $userFunctions->restaurantId = $userOrder->restaurant_id;
            $userTransaction = $userFunctions->getFirstTranSactionUser();

            if ($userTransaction == 0) {
                $userFunctions->total_order = $userTransaction;
            } else {
                $userFunctions->total_order = 1;
            }

            $orderPoints = $orderFunctions->user_order_point;
            $userPointsOrder = $userPoints + $orderPoints;
            ########Dine and more awards point calculation ########
            $userFunctions->userId = $userId;

            $userFunctions->order_amount = $orderFunctions->user_order_point;
            $userFunctions->activityDate = $userOrder->delivery_time;
            $userFunctions->restaurant_name = $orderFunctions->restaurantName;
            $userFunctions->orderId = $userOrderId;
            $userFunctions->typeValue = $userOrderId;
            $userFunctions->typeKey = 'order_id';
            $userFunctions->orderType = $userOrder->order_type;
            $awardPoint = $userFunctions->dineAndMoreAwards("order");

            if (isset($awardPoint['points'])) {
                $orderPoints = $awardPoint['points'];
                $userPointsOrder = $userPoints + $orderPoints;
            }
            //$userreferral->sendReferralMailUserInviter($userId,$userOrder->restaurant_id,$orderPoints);
            $restDetails = $userFunctions->getRestOrderFeatures($userOrder->restaurant_id);
            $cleverTap = array(
                "user_id" => $userOrder->user_id,
                "name" => (isset($userOrder->lname) && !empty($userOrder->lname)) ? $userOrder->fname . " " . $userOrder->lname : $userOrder->fname,
                "orderid" => $userOrderId,
                "identity" => $userOrder->email,
                "restaurant_name" => $orderFunctions->restaurantName,
                "restaurant_id" => $userOrder->restaurant_id,
                "eventname" => "order",
                "earned_points" => $orderPoints,
                "paid_with_point" => ($orderFunctions->discountAmountOnPoint == 0) ? 0 : floatval($orderFunctions->discountAmountOnPoint),
                "paid_with_card" => ($userOrder->pay_via_card == 0 || $cod == 1) ? 0 : floatval($userOrder->pay_via_card),
                "is_register" => ($userOrder->user_id && $userOrder->user_id != 0) ? "yes" : "no",
                "event" => 1,
                "order_type" => $userOrder->order_type,
                "order_date" => date("Y-m-d", strtotime($userOrder->delivery_time)),
                "order_time" => date("H:i", strtotime($userOrder->delivery_time)),
                "order_amount" => $userOrder->total_amount,
                "first_order" => ($userFunctions->total_order) ? "no" : "yes",
                "delivery_enabled" => $restDetails['delivery'],
                "takeout_enabled" => $restDetails['takeout'],
                "reservation_enabled" => $restDetails['reservations'],
                "deal_offer" => (isset($userOrder->deal_id) && !empty($userOrder->deal_id)) ? "yes" : "no",
                "promo_offer"=>(isset($userOrder->promocode_discount) && $userOrder->promocode_discount>0)?"yes":"no",
                "host_url" => $userOrder->host_name
            );


            $userFunctions->createQueue($cleverTap, 'clevertap');
            $orderFunctions->host_name = (isset($hostName) && !empty($hostName)) ? $hostName : PROTOCOL . SITE_URL;

            if ($orderFunctions->host_name != PROTOCOL . SITE_URL && $orderFunctions->isAppliedPromo == 1) {
                $orderFunctions->user_id = $userOrder->user_id;
                $orderFunctions->order_id = $userOrderId;
                $orderFunctions->addUserPromocode($userPromocodesDetails);
            }
            return array(
                'id' => $userOrderId,
                'receipt' => $userOrder->payment_receipt,
                'points' => (string) (int) $userPointsOrder,
                'orderpoints' => (string) (int) $orderPoints,
                'order_status' => $userOrder->status,
                'redeem_point' => ($orderFunctions->point == 0) ? "" : (string) $orderFunctions->point,
                'pay_via_point' => ($orderFunctions->discountAmountOnPoint == 0) ? "" : (string) $orderFunctions->discountAmountOnPoint,
                'pay_via_card' => ($userOrder->pay_via_card == 0 || $cod == 1) ? "" : (string) $userOrder->pay_via_card,
                'pay_via_cash' => ($cod == 1) ? (string) number_format($userOrder->total_amount - $orderFunctions->discountAmountOnPoint, 2) : '',
                'tax' => $userOrder->tax,
                'delivery_charge' => $userOrder->delivery_charge,
                'tip_amount' => $userOrder->tip_amount,
                'deal_title' => $userOrder->deal_title,
                'deal_discount' => $userOrder->deal_discount,
                'deal_type' => $orderFunctions->deal_type,
                'subTotal' => $orderFunctions->subtotal,
                'tip_percent' => $userOrder->tip_percent,
                'promocode_discount' => $userOrder->promocode_discount,
                'finalTotal' => $userOrder->total_amount,
                'card_type' => $userOrder->card_type,
                'expired_on' => $userOrder->expired_on,
                'card_number' => $userOrder->card_number,
                'delivery_time' => $userOrder->delivery_time,
                'cod' => $cod,
                'dm_register' => $dm_register
            );
//            }
        } catch (MunchException $e) {
//            if (!$isPreOrderReservation) {
            $dbtable->getWriteGateway()->getAdapter()->getDriver()->getConnection()->rollback();
//            }
            $options = array(
                'cart_data' => json_encode($data),
                'exception' => $e->getMessage(),
                'origin' => 'File:' . $e->getFile() . '|Line:' . $e->getLine(),
                'restaurant_id' => $data ['order_details'] ['restaurant_id'],
            );
            \MUtility\MunchLogger::writeAbandonedCartToDb($options);
            throw new \Exception($e->getCustomMessage(), 400);
        }
    }

    private function savePreOrderReservation($data, $userId, $orderPass) {

        $orderReturnData = array();
        $dbtable = new UserOrderTable ();
        $userFunctions = new UserFunctions ();
        $userModel = new User();
        $userId = $this->getUserSession()->getUserId();
        $config = $this->getServiceLocator()->get('Config');
        $webUrl = PROTOCOL . $config ['constants'] ['web_url'];
        $userreferral = new UserReferrals();
        $userPoints = 0;
        $userPointsOrder = 0;
        $orderPoints = 0;

        if ($userId) {
            $userPointsCount = new \User\Model\UserPoint();
            $userPointsSum = $userPointsCount->countUserPoints($userId);
            $redeemPoint = $userPointsSum[0]['redeemed_points'];
            $userPoints = strval($userPointsSum[0]['points'] - $redeemPoint);
        }
        $dbtable->getWriteGateway()->getAdapter()->getDriver()->getConnection()->beginTransaction();
        try {
            //$dbtable = new UserOrderTable ();          
            $orderData = $data;
            /* Create order */
            $data['do_transaction'] = false;
            $data["is_preorder_reservation"] = false;
            $orderReturnData = $this->create($orderData);

            if (isset($orderReturnData['error'])) {
                throw new \Exception($orderReturnData['error']);
            }
            ###########################################   

            $returnData = array("order" => $orderReturnData);

            if (isset($data["reservation_details"]['reservation_id']) && !empty($data["reservation_details"]['reservation_id'])) {
                $userReservation = new UserReservation();
                $data["reservation_details"]['time_slot'] = date("Y-m-d H:i", strtotime($data["reservation_details"]['time_slot']));
                $data["reservation_details"]['time'] = date("H:i", strtotime($data["reservation_details"]['time']));
                $updateOrderIdData = array(
                    'order_id' => $orderReturnData['id'],
                    'party_size' => $data["reservation_details"]['reserved_seats'],
                    'reserved_seats' => $data["reservation_details"]['reserved_seats'],
                    'time_slot' => $data["reservation_details"]['time_slot'],
                );
                $userReservation->id = $data["reservation_details"]['reservation_id'];
                $userReservation->update($updateOrderIdData);
                $resDetail = $userReservation->getUserReservation(array(
                    'columns' => array(
                        'restaurant_name',
                        'receipt_no'
                    ),
                    'where' => array(
                        'id' => $data["reservation_details"]['reservation_id']
                    )
                ));

                $updateReturn = $userReservation->update($updateOrderIdData);
                if ($updateReturn) {
                    $userTransaction = $userFunctions->getFirstTranSactionUser();
                    if ($userTransaction == 0) {
                        $orderPoints = $orderReturnData['orderpoints'] + 110;
                        $userPointsOrder = $userPoints + $orderPoints;
                    } else {
                        $orderPoints = $orderReturnData['orderpoints'] + 10;
                        $userPointsOrder = $userPoints + $orderPoints;
                    }

                    $returnData["reservation"]['reservation_id'] = $data["reservation_details"]['reservation_id'];
                    $returnData["reservation"]['receipt_no'] = $resDetail[0]['receipt_no'];
                    $returnData["reservation"]['date'] = $data["reservation_details"]['date'];
                    $returnData["reservation"]['time'] = $data["reservation_details"]['time'];
                    $returnData["reservation"]['reserved_seats'] = $data["reservation_details"]['reserved_seats'];
                    $returnData["reservation"]['time_slot'] = $data["reservation_details"]['time_slot'];
                    $data["reservation_details"]['restaurant_name'] = $resDetail[0]['restaurant_name'];
                    $returnData["reservation"]['points'] = $userPoints;
                    $returnData["reservation"]['orderpoints'] = (int) $orderPoints;
                    $returnData['order']['points'] = $userPoints;
                    $returnData['order']['orderpoints'] = (int) $orderPoints;
                    $returnData['order']['redeem_point'] = "";
                    $returnData['order']['pay_via_point'] = "";
                    $returnData['order']['pay_via_card'] = "";
                }
            } else {
                $reservationController = $this->getServiceLocator()->get("Restaurant\Controller\WebReservationController");
                $reservationData = $data["reservation_details"];
                $reservationData['token'] = $data['token'];
                $reservationData["order_id"] = $orderReturnData["id"];
                $reservationData["order_point"] = $orderReturnData["orderpoints"];
                $reservationData["finalTotal"] = $orderReturnData["finalTotal"];
                $reservationReturnData = $reservationController->create($reservationData);
                if (isset($reservationReturnData['error']) && $reservationReturnData['error'] == 1) {
                    throw new \Exception($reservationReturnData['msg']);
                }
                $returnData['order']['points'] = $reservationReturnData['points'];
                $returnData['order']['orderpoints'] = $reservationReturnData['orderpoints'];
                $returnData["reservation"] = $reservationReturnData;
                $returnData["reservation"]['reservation_status'] = 1;
                $returnData["order"]['dine_more_wards'] = $reservationReturnData['dine_more_wards'];
                $returnData['order']['redeem_point'] = $orderReturnData['redeem_point'];
                $returnData['order']['pay_via_point'] = $orderReturnData['pay_via_point'];
                $returnData['order']['pay_via_card'] = $orderReturnData['pay_via_card'];
            }
            $dbtable->getWriteGateway()->getAdapter()->getDriver()->getConnection()->commit();
            //referral mail when change normal reservation to perpaid reservation
            //$userreferral->sendReferralMailUserInviter($userId,$reservationReturnData['restaurant_id']);
        } catch (\Exception $ex) {
            $dbtable->getWriteGateway()->getAdapter()->getDriver()->getConnection()->rollback();
            throw new \Exception($ex->getMessage(), 400);
        }

        unset($returnData['order']['subTotal']);
        unset($returnData['order']['tax']);
        unset($returnData['order']['tipAmount']);
        unset($returnData['order']['dealDiscount']);
        unset($returnData['order']['promocodeDiscount']);
        unset($returnData['order']['finalTotal']);
        return $returnData;
    }

}
