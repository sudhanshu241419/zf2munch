<?php
use MCommons\StaticOptions;
class MStripe {
	public static $stripeKey;
	function __construct($key = null) {
		if(!$key){
			$config = StaticOptions::getServiceLocator()->get('config');
			if (isset($config['constants']['stripe']['secret_key'])) {
				$key = $config['constants']['stripe']['secret_key'];
			}
		}
		self::$stripeKey = $key;
		Stripe::setApiKey ( self::$stripeKey );
	}
	public static function addCard($cardDetails, $cuId = NULL) {
		if ($cuId != NULL) {
			$cu = Stripe_Customer::retrieve ( $cuId );
			$newCard = $cu->cards->create ( array (
					"card" => $cardDetails 
			) );
		} else {
			$newCard = Stripe_Customer::create ( array (
					"description" => "Customer for MunchAdo",
					"card" => $cardDetails 
			) );
			$newCard = $newCard->cards->data [0];
		}
		try {
			$cardResponse = array ();
			if (is_object ( $newCard )) {
				$cardResponse ['id'] = $newCard->id;
				$cardResponse ['object'] = $newCard->object;
				$cardResponse ['last4'] = $newCard->last4;
				$cardResponse ['type'] = $newCard->type;
				$cardResponse ['exp_month'] = $newCard->exp_month;
				$cardResponse ['exp_year'] = $newCard->exp_year;
				$cardResponse ['fingerprint'] = $newCard->fingerprint;
				$cardResponse ['customer'] = $newCard->customer;
				$cardResponse ['country'] = $newCard->country;
				$cardResponse ['name'] = $newCard->name;
				$cardResponse ['address_line1'] = $newCard->address_line1;
				$cardResponse ['address_line2'] = $newCard->address_line2;
				$cardResponse ['address_city'] = $newCard->address_city;
				$cardResponse ['address_state'] = $newCard->address_state;
				$cardResponse ['address_zip'] = $newCard->address_zip;
				$cardResponse ['address_country'] = $newCard->address_country;
				$cardResponse ['cvc_check'] = $newCard->cvc_check;
				$cardResponse ['address_line1_check'] = $newCard->address_line1_check;
				$cardResponse ['address_zip_check'] = $newCard->address_zip_check;
				$cardResponse ['address_zip_check'] = $newCard->address_zip_check;
				
				
			}
			return array (
					'response' => $cardResponse,
					'message' => 'success',
					'status' => 1 
			);
		} catch ( \Exception $ex ) {
			return array (
					'response' => NULL,
					'message' => $ex->getMessage (),
					'status' => 0 
			);
		}
	}
	public static function chargeCard($cardDetails, $amount,$restaurantName = false) {
		try {
			if (is_array ( $cardDetails )) {
				$charge = Stripe_Charge::create ( array (
						'card' => $cardDetails,
						'amount' => $amount*100,
						'currency' => 'usd',
                        'description'=>"MunchAdo-(".$restaurantName.")"
				) );
			} else {
				$charge = Stripe_Charge::create ( array (
						"amount" => $amount*100,
						"currency" => "usd",
						"customer" => $cardDetails,
                        "description"=>"MunchAdo-(".$restaurantName.")"
				) );
			}
           // pr($charge,1);
			$response = json_decode ( $charge, true );
			$response['status'] = 1;
			return $response;
		} catch ( \Exception $ex ) {
			return array (
					'response' => NULL,
					'message' => $ex->getMessage (),
					'status' => 0 
			);
		}
	}
	
	public static function retrive($id){
		$response=Stripe_Charge::retrieve($id);
		$response = json_decode ( $response, true );
		return $response;
	}
}