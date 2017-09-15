<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use MCommons\StaticOptions;
use Zend\Http\PhpEnvironment\Request;

class UsermenureviewController extends AbstractRestfulController {
	public function create($data) {
		
		try {
			$restaurent_id = $data ['restaurent_id'];
			$request = new Request ();
			$files = $request->getFiles ();
			if (! empty ( $files )) {
				$response = StaticOptions::uploadUserImages ( $files, APP_PUBLIC_PATH, TEMP_USER_MENU_IMG_PATH . DS . "rest_" . $restaurent_id . DS );
				if (empty ( $response )) {
					throw new \Exception ( "No files to upload", 400 );
				}
			} else {
				throw new \Exception ( "Invalid Parameters", 400 );
			}
		} catch ( \Exception $excp ) {
			return $this->sendError ( array (
					'error' => $excp->getMessage () 
			), $excp->getCode () );
		}
		return $response;
	}
}
