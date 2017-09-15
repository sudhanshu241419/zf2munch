<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\UserFunctions;

class FacebookShareController extends AbstractRestfulController {

    public function create($data) {  
        return true; //As per yogendra sir, no point will give; 17 mar, 2017
        $session = $this->getUserSession();
        $user_id = $session->getUserId();
        $userFunctions = new UserFunctions();
        $type = isset($data['type'])?$data['type']:"";
        if($type==='fb'){
            $identifier = 'facebookShare';
            $points = $userFunctions->getAllocatedPoints($identifier); 
            $message = "Thanks for getting the word out. Here are ".$points ['points']." points. Spend them all in one place. MunchAdo.com."; 
            $userFunctions->givePoints($points, $user_id, $message);
        }elseif($type==='tw'){
            $identifier = 'twitterShare';
            $points = $userFunctions->getAllocatedPoints($identifier); 
            $message = "Thanks for getting the word out. Here are ".$points ['points']." points. Spend them all in one place. MunchAdo.com."; 
            $userFunctions->givePoints($points, $user_id, $message);
        }elseif($type ==='both'){
            $identifier = 'twitterShare';
            $points = $userFunctions->getAllocatedPoints($identifier); 
            $message = "Thanks for getting the word out. Here are ".$points ['points']." points. Spend them all in one place. MunchAdo.com."; 
            $userFunctions->givePoints($points, $user_id, $message);
            
            $identifier = 'facebookShare';
            $points1 = $userFunctions->getAllocatedPoints($identifier); 
            $message = "Thanks for getting the word out. Here are ".$points ['points']." points. Spend them all in one place. MunchAdo.com."; 
            $userFunctions->givePoints($points1, $user_id, $message);           
            
        }else{
            throw new \Exception ( "Required share type", 400 );
        }
        
        
        return $response = array('success'=>true);
    }

}
