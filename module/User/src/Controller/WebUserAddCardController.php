<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use MStripe;
use User\Model\UserCard;
use User\UserFunctions;
use Restaurant\OrderFunctions;

class WebUserAddCardController extends AbstractRestfulController {

    public function create($data) {
        $response = array();
        try {
            if (!empty($data)) {
                $session = $this->getUserSession();
                $isLoggedIn = $session->isLoggedIn();
                if ($isLoggedIn) {
                    $data['user_id'] = $session->getUserId();
                } else {
                    throw new \Exception("User unavailable", 400);
                }          
                
                if (!isset($data['card_number'])) {
                    throw new \Exception("Credit Card dose not exists", 400);
                }
                if (!isset($data['exp_month'])) {
                    throw new \Exception("Expiry month dose not exists", 400);
                }
                if (!isset($data['exp_year'])) {
                    throw new \Exception("Expiry year dose not exists", 400);
                }

                if (isset($data['billing_zip'])) {
                    $billing_zip = $data['billing_zip'];
                } else {
                    throw new \Exception("Billing zip dose not exists", 400);
                }
                
                $use_card_model = new UserCard();   
                $userFunctions = new UserFunctions();
                
                $cardDetails = array(
                    'number' => $data['card_number'],
                    'exp_month' => $data['exp_month'],
                    'exp_year' => $data['exp_year'],
                    'name' => isset($data['name_on_card']) ? $data['name_on_card'] : "",
                    'cvc' => isset($data['cvc']) ? $data['cvc'] : "",
                    'address_zip' => isset($data['billing_zip']) ? $data['billing_zip'] : ''
                );
                    try {
                           $response = $userFunctions->saveCardToStripeAndDatabase($cardDetails,$saveCard=1);
                           return $response;
                        } catch (\Exception $e) {
                           throw new \Exception($e->getMessage(), 400);
                        }             
                
            }else{
                throw new \Exception("Invalid card detail", 400);
            }
        } catch (\Exception $ex) {
            return $this->sendError(array(
                        'error' => $ex->getMessage()
                            ), $ex->getCode());
        }
    }

    public function getList() {

        $user_function = new UserFunctions();
        $data = array();
        $session = $this->getUserSession();
        $isLoggedIn = $session->isLoggedIn();
        $restaurantId = $this->getQueryParams('rest_id',false);
        if ($isLoggedIn) {
            $user_id = $session->getUserId();
        } else {
            throw new \Exception("User unavailable", 400);
        }
        
        $orderPass = 0;
        if($restaurantId){
            $restaurantModel = new \Restaurant\Model\Restaurant();
            $optionOrderPass = array('columns'=>array('order_pass_through'),'where'=>array('id'=>$restaurantId));
            $orderPassThrough = $restaurantModel->findRestaurant($optionOrderPass)->toArray();
            $orderPass = $orderPassThrough['order_pass_through'];  
        }
                
        $use_card_model = new UserCard();
        $locationData = $session->getUserDetail('selected_location');
        $currentDate = strtotime($user_function->userCityTimeZone($locationData));
        $currentMonth = date("n", $currentDate);
        $currentYear = date("Y", $currentDate);
        $response = $use_card_model->fetchUserCard($user_id,$orderPass);
        $k = 0;        
        foreach ($response as $key => $val) {
            $date = explode('/', $val['expired_on']);
            $months = $date[0];
            $year = substr($date[1], - 2);
            $cardValidate = 0;

            if ($currentYear < $date[1]) {
                $cardValidate = 1;
            } elseif ($currentYear == $date[1]) {
                if ($months >= $currentMonth) {
                    $cardValidate = 1;
                } else {
                    $cardValidate = 0;
                }
            } else {
                $cardValidate = 0;
            }

            
            if(($restaurantId) && $cardValidate ==1){ 
                $data[$k]['id'] = $val['id'];
                $data[$k]['card_number'] = $val['card_number'];
                $data[$k]['card_type'] = $val['card_type'];
                $data[$k]['name_on_card'] = $val['name_on_card'];
                $data[$k]['stripe_token_id'] = $val['stripe_token_id'];
                $data[$k]['zipcode'] = $val['zipcode'];
                $data[$k]['status'] = $val['status'];
                $data[$k]['expired_on'] = $months . '/' . $year;
                $data[$k]['expiry_month'] = $months;
                $data[$k]['expiry_year'] = $year;
                $data[$k]['billing_zip'] = $val['zipcode'];
                $data[$k]['is_expired'] = ($cardValidate==1)?0:1;
                
                if ($k == 0)
                    $data[$k]['default'] = '1';
                else
                    $data[$k]['default'] = '0';
                $k++;
            }elseif(!$restaurantId){
                $data[$k]['id'] = $val['id'];
                $data[$k]['card_number'] = $val['card_number'];
                $data[$k]['card_type'] = $val['card_type'];
                $data[$k]['name_on_card'] = $val['name_on_card'];
                $data[$k]['stripe_token_id'] = $val['stripe_token_id'];
                $data[$k]['zipcode'] = $val['zipcode'];
                $data[$k]['status'] = $val['status'];
                $data[$k]['expired_on'] = $months . '/' . $year;
                $data[$k]['expiry_month'] = $months;
                $data[$k]['expiry_year'] = $year;
                $data[$k]['billing_zip'] = $val['zipcode'];
                $data[$k]['is_expired'] = ($cardValidate==1)?0:1;

                if ($k == 0)
                    $data[$k]['default'] = '1';
                else
                    $data[$k]['default'] = '0';
                $k++;
            }
                
           
        }
        return $data;
    }

    public function delete($card_id) {
        $session = $this->getUserSession();
        $isLoggedIn = $session->isLoggedIn();
        if ($isLoggedIn) {
            $user_id = $session->getUserId();
        } else {
            throw new \Exception("User unavailable", 400);
        }
        $use_card_model = new UserCard();
        $use_card_model->id = $card_id;
        $deleted = $use_card_model->delete();

        return array(
            "deleted" => (bool) $deleted
        );
    }

}
