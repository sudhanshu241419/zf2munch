<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\UserFunctions;

class ForgotPasswordController extends AbstractRestfulController {
	public function update($id, $data) {
		$userFunctions = new UserFunctions ();        
        $data['user_source'] = \MCommons\StaticOptions::$_userAgent;
		$userFunctions->changePassword ( $data );
		return array (
				'success' => 'true' 
		);
	}
}
