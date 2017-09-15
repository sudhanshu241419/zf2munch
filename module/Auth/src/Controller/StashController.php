<?php

namespace Auth\Controller;

use MCommons\Controller\AbstractRestfulController;
use Auth\Model\Auth;
class StashController extends AbstractRestfulController {
	public function delete($id) {
		$authModel = new Auth ();
		$deleted = $authModel->delete ();
               
		return array (
				"deleted" => ( bool ) $deleted 
		);
	}
}