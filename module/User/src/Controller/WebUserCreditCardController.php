<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use MStripe;
use User\Model\UserCard;
use Restaurant\OrderFunctions;

class WebUserCreditCardController extends AbstractRestfulController {

    public function create($data) {

        try {
            if (empty($data)) {
                throw new \Exception("Invalid Parameters", 400);
            }
            $use_card_model = new UserCard ();
            $stripe = new MStripe($this->getStripeKey());
            $session = $this->getUserSession();
            if (!$session->isLoggedIn()) {
                throw new \Exception("User unavailable", 10);
            }
            $data ['user_id'] = $session->getUserId();
            $cust_id = NULL;
            if (!isset($data['name'])) {
                throw new \Exception("Name on card can't be empty", 400);
            }
            if (!isset($data ['card_number'])) {
                throw new \Exception("Credit Card dose not exists", 400);
            }
            if (!isset($data ['exp_month'])) {
                throw new \Exception("Expiry month dose not exists", 400);
            }
            if (!isset($data ['exp_year'])) {
                throw new \Exception("Expiry year dose not exists", 400);
            }
            if (!isset($data['billing_zip'])) {
                throw new \Exception("Billing zip can't be empty", 400);
            }
            $cardDetails = array(
                'number' => $data ['card_number'],
                'exp_month' => $data ['exp_month'],
                'exp_year' => $data ['exp_year'],
                'name' => isset($data ['name']) ? $data ['name'] : "",
                'cvc' => isset($data ['cvc']) ? $data ['cvc'] : ""
            );
            $uDetails = $use_card_model->fetchUserCard($data ['user_id']);
            if (!empty($uDetails)) {
                $cust_id = $uDetails [0] ['stripe_token_id'];
            }
            $repsponse = $stripe->addCard($cardDetails, $cust_id);

            if ($repsponse ['status']) {
                if ($cust_id == NULL) {
                    $orderFunctions = new OrderFunctions();
                    $use_card_model->user_id = $data ['user_id'];
                    $use_card_model->card_number = $repsponse ['response'] ['last4'];
                    $use_card_model->card_type = $repsponse ['response'] ['type'];
                    $use_card_model->name_on_card = $repsponse ['response'] ['name'];
                    $use_card_model->stripe_token_id = $repsponse ['response'] ['customer'];
                    $use_card_model->expired_on = $repsponse ['response'] ['exp_month'] . "/" . $repsponse ['response'] ['exp_year'];
                    $use_card_model->encrypt_card_number = $orderFunctions->aesEncrypt($data['card_number'] . "-" . $data ['cvc']);
                    $use_card_model->addCard();
                }
                $repsponse['card_id'] = $use_card_model->id;
                return $repsponse;
            } else {
                throw new \Exception($repsponse ['message'], 400);
            }
        } catch (\Exception $ex) {
            //\MUtility\MunchLogger::writeLog($ex, $ex->getCode());
            return $this->sendError(array(
                        'error' => $ex->getMessage()
                            ), $ex->getCode());
        }
    }

    public function delete($card_id) {
        $session = $this->getUserSession();
        $isLoggedIn = $session->isLoggedIn();
        if ($isLoggedIn) {
            $user_id = $session->getUserId();
        } else {
            throw new \Exception("User unavailable", 400);
        }
        $cardModel = new UserCard ();
        $cardModel->id = $card_id;
        $cardModel->user_id = $user_id;
        $deleted = $cardModel->delete();

        return array(
            "deleted" => (bool) $deleted
        );
    }

    public function getList() {
        $data = array();
        $orderFunctions = new \Restaurant\OrderFunctions();
        $session = $this->getUserSession();
        $isLoggedIn = $session->isLoggedIn();
        $restaurantId = $this->getQueryParams('rest_id', false);
        $orderPass = 0;
        if ($restaurantId) {
            $restaurantModel = new \Restaurant\Model\Restaurant();
            $optionOrderPass = array('columns' => array('order_pass_through'), 'where' => array('id' => $restaurantId));
            $orderPassThrough = $restaurantModel->findRestaurant($optionOrderPass)->toArray();
            $orderPass = $orderPassThrough['order_pass_through'];
        }
        if ($isLoggedIn) {
            $user_id = $session->getUserId();
        } else {
            throw new \Exception("User unavailable", 400);
        }
        $use_card_model = new UserCard ();
        $response = $use_card_model->fetchUserCard($user_id);
        if ($response) {
            foreach ($response as $key => $val) {
                $data[] = array_intersect_key($val, array_flip(array('id', 'card_number', 'card_type', 'expired_on')));
            }
        }

        return $data;
    }

}
