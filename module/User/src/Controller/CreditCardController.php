<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use MStripe;
use User\Model\UserCard;

class CreditCardController extends AbstractRestfulController {
	public function create($data) {
		$response = array ();
		try {
			if (! empty ( $data )) {
				$session = $this->getUserSession ();
				$isLoggedIn = $session->isLoggedIn ();
				if ($isLoggedIn) {
					$data ['user_id'] = $session->getUserId ();
				} else {
					throw new \Exception ( "User unavailable", 400 );
				}
				$cust_id = NULL;
				if (! isset ( $data ['card_number'] )) {
					throw new \Exception ( "Credit Card dose not exists", 400 );
				}
				if (! isset ( $data ['exp_month'] )) {
					throw new \Exception ( "Expiry month dose not exists", 400 );
				}
				if (! isset ( $data ['exp_year'] )) {
					throw new \Exception ( "Expiry year dose not exists", 400 );
				}				
				if (isset ( $data ['billing_zip'] )) {
					$billing_zip = $data ['billing_zip'];
				}
                $userFunctions = new \User\UserFunctions();
				$cardDetails = array (
                    'number' => $data ['card_number'],
                    'exp_month' => $data ['exp_month'],
                    'exp_year' => $data ['exp_year'],
                    'name' => isset ( $data ['name'] ) ? $data ['name'] : "",
                    'cvc' => isset ( $data ['cvc'] ) ? $data ['cvc'] : "",
                    'address_zip' => isset ( $data ['billing_zip'] ) ? $data ['billing_zip'] : '' 
				);
               
                
                 try {
                        $response = $userFunctions->saveCardToStripeAndDatabaseMob($cardDetails);
                     } catch (\Exception $e) {
                        throw new \Exception("Card detail already exist", 400);
                     } 
               
                $response1 ['status'] = $response ['status'];
                $response1 ['id'] = $response ['card_inserted_id'];
                $response1 ['card_no'] = $response ['last4'];
                $response1 ['type'] = $response ['type'];
                $response1 ['exp_month'] = $response ['exp_month'];
                $response1 ['exp_year'] = $response ['exp_year'];
                $response1 ['billing_zip'] = $response ['address_zip'];					
                return $response1;
                
			} else {
				throw new \Exception ( "Invalid credit card detail", 400 );
			}
		} catch ( \Exception $ex ) {
//			return $this->sendError ( array (
//					'error' => $ex->getMessage () 
//			), $ex->getCode () );
            throw new \Exception("Card detail already exist", 400);
		}
	}
	public function getList() {
        try{
		$session = $this->getUserSession ();
		$isLoggedIn = $session->isLoggedIn ();
		if ($isLoggedIn) {
			$user_id = $session->getUserId ();
		} else {
			throw new \Exception ( "User unavailable", 400 );
		}
		$use_card_model = new UserCard ();
		$response = $use_card_model->fetchUserCard ( $user_id );
        unset($response['stripe_token_id']);
		foreach ( $response as $key => $val ) {
			if ($key == 0)
				$response [$key] ['default'] = '1';
			else
				$response [$key] ['default'] = '0';
            // unset($response[$key]['stripe_token_id']);
		}
		
		return $response;
        } catch (\Exception $e) {
           \MUtility\MunchLogger::writeLog($e, 1,'Something Went Wrong On credit card Api');
           throw new \Exception($e->getMessage(),400);
        }
	}
	public function delete($card_id) {
        try{
		$session = $this->getUserSession ();
		$isLoggedIn = $session->isLoggedIn ();
		if ($isLoggedIn) {
			$user_id = $session->getUserId ();
		} else {
			throw new \Exception ( "User unavailable", 400 );
		}
		$use_card_model = new UserCard ();
		$use_card_model->id = $card_id;
		$deleted = $use_card_model->delete ();
		
		return array (
				"deleted" => ( bool ) $deleted 
		);
        } catch (\Exception $e) {
           \MUtility\MunchLogger::writeLog($e, 1,'Something Went Wrong On credit card Api');
           throw new \Exception($e->getMessage(),400);
        }
	}
}