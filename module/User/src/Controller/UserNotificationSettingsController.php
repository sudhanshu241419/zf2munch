<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\Model\UserSetting;

class UserNotificationSettingsController extends AbstractRestfulController {
	const FORCE_LOGIN = true;
	public function getList() {
		$session = $this->getUserSession ();
		$user_id = $session->getUserId ();
		$userSettingsModel = new UserSetting ();
		$options = array (
				'columns' => array (
                        'id',
                        'new_order',
						'order_confirmation',
						'new_reservation',
                        'comments_on_reviews',
						'reservation_confirmation',		
                        'friend_request',
						'system_updates'						 
				),
				'where' => array (	
						'user_id' => $user_id 
				) 
		);
		$userSettingsModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
		$response = current($userSettingsModel->find ( $options )->toArray());        
        if(!empty($response)){
            foreach($response as $key => $val){
                if($val==null){
                    $response[$key] = "0";
                }
            }
        }else{
		
			throw new \Exception('No Details Found');
		}
		return $response;
	}
	public function update($id, $data){
		$session = $this->getUserSession();
		$user_id = $session->getUserId();
        if(!$user_id){
            throw new \Exception ( "Woah now, You are not valid user.", 400 );
		}
        if(empty($data)){
            throw new \Exception('You did not provide anything to update');
        }
        unset($data['token']);
		$userSettingsModel = new UserSetting();
		$userSettingsModel->user_id = $user_id;
        $options = array (
            'columns' => array (
                    'id',
                    'new_order',
                    'order_confirmation',
                    'new_reservation',
                    'comments_on_reviews',
                    'reservation_confirmation',		
                    'friend_request',
                    'system_updates'						 
            ),
            'where' => array (	
                    'user_id' => $user_id 
            ) 
        );
		$userSettingsModel->getDbTable()->setArrayObjectPrototype('ArrayObject');		
        $response1 = $userSettingsModel->find ( $options )->toArray();		
		
            if((isset($data['id']) && !empty($data['id'])) || ($response1)){  
                $userSettingsModel->id=$response1[0]['id'];
                $userSettingsModel->update($data);
                $response = $userSettingsModel->find ( $options )->toArray();
                return $response = $response[0];
            }else{
                $data['user_id']=$user_id;
                $insertedId = $userSettingsModel->insert($data);
                $data['id'] = $insertedId;  
                return $data;
            }         
		
		throw new \Exception('Something Apparently Went Wrong');
	}
}