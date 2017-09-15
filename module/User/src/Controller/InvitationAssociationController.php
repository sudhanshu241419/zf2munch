<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\Model\User;

class InvitationAssociationController extends AbstractRestfulController {
	const FORCE_LOGIN = true;
	public function create($data) {
        $userFunctions = new \User\UserFunctions();
        $open_page_type = (isset($data['open_page_type']) && !empty($data['open_page_type']))?$data['open_page_type']:"";
        $refId = (isset($data['refId']) && !empty($data['refId']))?$data['refId']:"";
        $userId = (isset($data['userId']) && !empty($data['userId']))?$data['userId']:"";
        $userEmail = (isset($data['email']) && !empty($data['email']))?$data['email']:"";
        if($userId != $refId){
        $response = $userFunctions->associateInvitation($open_page_type,$refId,$userId,$userEmail);
        }else{
            $response = false;
        }
        return array('result'=>$response);
       
	}	
	
}