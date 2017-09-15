<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use MStripe;

class ValidateCreditCardController extends AbstractRestfulController {
	public function create($data) {
        	
        $stripeModel = new MStripe (); 
        $cardDetails = array(
                        'number' => '4242424242424241',
                        'exp_month' => '06',
                        'exp_year' => '17',
                        'name' => 'sudhanshu',
                        'cvc' => '123',
                        
                    );
        unset($data['token']);
        $add_card_response = $stripeModel->addCard($cardDetails);
		print_r($add_card_response);
		die;
	}
}
