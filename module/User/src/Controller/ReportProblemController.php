<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\Model\ReportAbuse;

class ReportProblemController extends AbstractRestfulController {
	public function create($data) {
		$reportAbuse = new ReportAbuse ();
        $userFunctions = new \User\UserFunctions();
		$session = $this->getUserSession ();
		$isLoggedIn = $session->isLoggedIn ();
        if ($isLoggedIn) {
            $data ['user_id'] = $session->getUserId ();
            $reportAbuse->user_id = ( int ) $data ['user_id'];
        }else{
            throw new \Exception ( 'User is not logged in', 400 );
        }
		try {
			if (! empty ( $data )) {			
				if (isset ( $data ['abuse_type'] )) {
                    if($data ['abuse_type'] == 'wrong'){
                        $abuseType = 'Factual errors';
                    }elseif($data ['abuse_type'] == 'offensive'){
                        $abuseType = 'Restaurant no longer exists';
                    }elseif($data ['abuse_type'] == 'other'){
                        $abuseType = 'Other';
                    }elseif($data ['abuse_type'] == 'duplicate'){
                        $abuseType = "Duplicate";
                    }
					$reportAbuse->abuse_type = $abuseType;
                }else {
					throw new \Exception ( "Abuse type is required", 400 );
				}
				if (isset ( $data ['restaurant_id'] )) {
					$reportAbuse->restaurant_id = $data ['restaurant_id'];
				} else {
					throw new \Exception ( "Restaurant id dose not exists", 400 );
				}
                
				$reportAbuse->review_id = isset($data ['review_id'])?$data ['review_id']:'';
				$reportAbuse->comment = isset($data ['comment'])?$data ['comment']:'';
				                  
				$response = $reportAbuse->addReport ();
                ####################### Assign points user for registration #######################
                $points = $userFunctions->getAllocatedPoints('reportError');        
                $message = 'You have report a problem! You\'ll need points to have the most fun, here take 5. Hoard them wisely.';
                $userFunctions->givePoints($points, $reportAbuse->user_id, $message); 
                #############################################################################
				if (! $response) {
					throw new \Exception ( 'Unable log report', 400 );
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