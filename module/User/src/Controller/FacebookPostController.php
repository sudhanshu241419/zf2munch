<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\UserFunctions;

class FacebookPostController extends AbstractRestfulController {

    public function create($data) { 
        return true; //As per yogendra sir, no point will give; 17 Mar, 2017
        try{
        $session = $this->getUserSession();
        $user_id = $session->getUserId();
        $userFunctions = new UserFunctions();
        $type = isset($data['type'])?$data['type']:"";
        if($type==='fb'){
            $identifier = 'postOnFacebook';
            $points = $userFunctions->getAllocatedPoints($identifier); 
            $message = "You have post on facebook! This calls for a celebration, here are ".$points ['points']." points!"; 
        }elseif($type==='tw'){
            $identifier = 'postOnTwitter';
            $points = $userFunctions->getAllocatedPoints($identifier); 
            $message = "You have post on twitter! This calls for a celebration, here are ".$points ['points']." points!";
        }else{
            throw new \Exception ( "Required post type", 400 );
        }
        
        $userFunctions->givePoints($points, $user_id, $message);
        return $response = array('success'=>true);
        } catch (\Exception $e) {
           \MUtility\MunchLogger::writeLog($e, 1,'Something Went Wrong On FacebookPost Api');
           throw new \Exception($e->getMessage(),400);
        }
    }

}
