<?php

use MCommons\StaticOptions;
use Zend\Json\Json;

//class Clevertap {
//    private $clevertap =array(
//          'apiurl'=>'https://api.clevertap.com/1/upload',
//          'X-CleverTap-Account-Id'=>'TEST-944-R78-884Z',
//          'X-CleverTap-Passcode'=>'QAA-IMW-CIAL'          
//        );
//    public static $config = array(
//        'adapter' => 'Zend\Http\Client\Adapter\Curl',
//        'curloptions' => array(
//            CURLOPT_FOLLOWLOCATION => true
//        )
//    );
//
//
//    public function upload($url,$data, $type) {                 
//        $accountid = $this->clevertap['X-CleverTap-Account-Id'];
//        $passcode = $this->clevertap['X-CleverTap-Passcode'];
//        $ch = curl_init($url);
//        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
//        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
//            'Content-Type: application/json',
//            'Content-Length: ' . strlen($data),
//            'X-CleverTap-Account-Id: '.$accountid,
//            'X-CleverTap-Passcode: '.$passcode
//                )
//        );
//        return curl_exec($ch);
//    }
//
//    public function uploadProfile($data) {   
//        $identity = $data['identity']; 
//        $data['email'] = $data['identity'];
//        unset($data['profile']);
//        unset($data['identity']);
//        $profile = array("d"=>array(array(
//            "identity"=>$identity,
//            "ts"=>time(),
//            "type"=>"profile",
//            "profileData"=>$data
//        )));      
//        
//        
//        $json = json_encode($profile, 1);
//       // pr($json,1);
//                
//        $apiurl = $this->clevertap['apiurl'];
//        $result = $this->upload($apiurl, $json, 'POST');   
//        MUtility\MunchLogger::writeLogCleverTap(new \Exception('clevertap'), 4, "RequestData(profile):".$json);
//        MUtility\MunchLogger::writeLogCleverTap(new \Exception('clevertap'), 4, "=================================================================");
//        MUtility\MunchLogger::writeLogCleverTap(new \Exception('clevertap'), 4, "ResponseData(profile):".json_encode($result));
//        MUtility\MunchLogger::writeLogCleverTap(new \Exception('clevertap'), 4, "=================================================================");
//        
//    }
//    
//     public function uploadEvent($data) {   
//        $identity = $data['identity'];
//        $data['email'] = $data['identity'];
//        $eventname = $data['eventname'];
//        unset($data['event'],$data['eventname'],$data['identity'],$data['email']);
//        $profile = array("d"=>array(array(
//            "identity"=>$identity,
//            "ts"=>time(),
//            "type"=>"event",
//            "evtName"=>$eventname,
//            "evtData"=>$data
//        )));      
//        
//        $json = json_encode($profile, 1);               
//        $apiurl = $this->clevertap['apiurl'];
//        $result = $this->upload($apiurl, $json, 'POST');  
//        
//        MUtility\MunchLogger::writeLogCleverTap(new \Exception('clevertap'), 4, "RequestData(Event):".$json);
//        MUtility\MunchLogger::writeLogCleverTap(new \Exception('clevertap'), 4, "===========================================================================================================================");
//        MUtility\MunchLogger::writeLogCleverTap(new \Exception('clevertap'), 4, "ResponseData(Event):".json_encode($result));
//        MUtility\MunchLogger::writeLogCleverTap(new \Exception('clevertap'), 4, "=================================================================");
//        
//    }
//}


################################ NETCORE ######################################

class Clevertap {
    private $version = array("v1","apiv2");
    private $netcore =array(
          'apiurl'=>'https://api.netcoresmartech.com',
          'apikey'=>'58b5dc3ec938fb539bb8d6967bacbc28',          
          'clientid'=>'ADGMOT35CHFLVDHBJNIG50K968IHHKCDLJANN9CGBV4A433SLPDG'
        );
    public static $config = array(
        'adapter' => 'Zend\Http\Client\Adapter\Curl',
        'curloptions' => array(
            CURLOPT_FOLLOWLOCATION => true
        )
    );
    private $activityType = array(
        "dine_and_more"=>102, 
        "bookmark"=>108, 
        "uncheck_bookmark"=>112, 
        "general"=>101, 
        "refer_friend"=>109, 
        "check_in"=>111,
        "upload_pic"=>107,
        "review"=>106,
        "order"=>103,
        "snag_a_spot"=>105,
        "reservation"=>105,
        "signed_to_app"=>110,
        "offer_availed" =>104        
        );


    public function upload($url,$data, $type) {                 
        $apikey = $this->netcore['apikey'];
        $apiurl = $this->netcore['apiurl'];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data),
            
                )
        );
        return curl_exec($ch);
    }

    public function uploadProfile($data) {   
        unset($data['profile']);
        unset($data['identity']);
        $firstname = "";
        $lastname = "";
        if(isset($data['name']) && !empty($data['name'])){
            $name = explode(" ", $data['name']);
            $firstname = $name[0];
            $lastname = isset($name[1])?$name[1]:"";
        }
        $postData = json_encode(array(            
            "EMAIL"=>$data['email'],            
            "REGISTRATION_DATE"=>isset($data['registration_date'])?$data['registration_date']:"",
            "HOST_URL"=>(isset($data['host_url']) && !empty($data['host_url']))?$data['host_url']:PROTOCOL.SITE_URL,
            "IS_REGISTER"=>$data['is_register'],
            "REGISTRATION_SOURCE"=>isset($data['registration_source'])?$data['registration_source']:"",
            "EARNED_POINTS"=>$data['earned_points'],
            "REDEEMED_POINTS"=>$data['redeemed_points'],
            "EARNED_DOLLAR"=>(string)$data['earned _dollar'],
            "REMAINING_POINTS"=>$data['remaining_points'],
            "REMAINING_DOLLAR"=>(string)$data['remaining_dollar'],
            "USER_ID"=>isset($data['user_id'])?$data['user_id']:"",
            "LAST_NAME"=>$lastname,
            "FIRST_NAME"=>$firstname,
            "CMS_REG"=>isset($data['cms_reg'])?$data['cms_reg']:"no"
        ));     
  
       
        $url = $this->netcore['apiurl']."/".$this->version[1]."?"."type=contact&activity=add";
        $readyData = array('apikey'=>$this->netcore['apikey'],"data"=>$postData);
        
        
        
        ##############
       //pr($postData);
        #############
        
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);        
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($readyData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        $info = curl_getinfo($ch);
        
                  
          
        MUtility\MunchLogger::writeLogCleverTap(new \Exception('netcore'), 4, "RequestData(add profile):".$postData);
        MUtility\MunchLogger::writeLogCleverTap(new \Exception('netcore'), 4, "=================================================================");
        MUtility\MunchLogger::writeLogCleverTap(new \Exception('netcore'), 4, "ResponseData(add profile):".$result);
        MUtility\MunchLogger::writeLogCleverTap(new \Exception('netcore'), 4, "=================================================================");
        
    }
    
     public function uploadEvent($data) {         
        $firstname = "";
        $lastname = "";
        if(isset($data['name']) && !empty($data['name'])){
            $name = explode(" ", $data['name']);
            $firstname = $name[0];
            $lastname = isset($name[1])?$name[1]:"";
        }
//        $story = "";
//        if($this->activityType['dine_and_more'] == $this->activityType[$data['eventname']]){
//            $sl = StaticOptions::getServiceLocator();
//            $config = $sl->get('Config');
//            $specChar = $config ['constants']['special_character'];
//            $story = isset($data['restaurant_id'])?  strip_tags(strtr($this->restaurantStory($data['restaurant_id']),$specChar)):"";
//        }
            
        $postData = json_encode(array(
            "activityid"=>$this->activityType[$data['eventname']],            
            "identity"=>$data['identity'],
            "activity_source"=>"CUS",
            "activity_params"=>array(
                    array(
                    "s^email"=>(string)$data['identity'],            
                    "s^restaurant_dine_more"=>isset($data['restaurant_dine_more'])?(string)$data['restaurant_dine_more']:"",
                    "s^restaurant_name"=>isset($data['restaurant_name'])?(string)$data['restaurant_name']:"",
                    "s^is_register"=>isset($data['is_register'])?$data['is_register']:"",
                    "s^restaurant_id"=>isset($data['restaurant_id'])?(string)$data['restaurant_id']:"",
                    "s^delivery_enabled"=>isset($data['delivery_enabled'])?(string)$data['delivery_enabled']:"",
                    "s^takeout_enabled"=>isset($data['takeout_enabled'])?(string)$data['takeout_enabled']:"",
                    "s^reservation_enabled"=>isset($data['reservation_enabled'])?(string)$data['reservation_enabled']:"",
                    "s^user_dine_more"=>isset($data['user_dine_more'])?(string)$data['user_dine_more']:"",
                    "s^point_earned"=>isset($data['earned_points'])?(string)$data['earned_points']:"",
                    "s^user_id"=>isset($data['user_id'])?(string)$data['user_id']:"",
                    "s^last_name"=>(string)$lastname,
                    "s^first_name"=>(string)$firstname,                    
                    "s^date"=>isset($data['event_date'])?(string)$data['event_date']:"",
                    "s^restaurant_story"=>"",
                    "s^orderid"=>isset($data['orderid'])?(string)$data['orderid']:"",
                    "s^paid_with_point"=>isset($data['paid_with_point']) ?(string)$data['paid_with_point'] :"0",
                    "s^paid_with_card"=>isset($data['paid_with_card'])?(string)$data['paid_with_card']:"0",
                    "s^order_date"=>isset($data['order_date'])?(string)$data['order_date']:"",
                    "s^order_time"=>isset($data['order_time'])?(string)$data['order_time']:"",
                    "s^order_type"=>isset($data['order_type'])?(string)$data['order_type']:"",
                    "s^type"=>isset($data['type'])?$data['type']:"",
                    "s^deal_offer"=>isset($data['deal_offer'])?$data['deal_offer']:"",
                    "s^promo_offer"=>isset($data['promo_offer'])?$data['promo_offer']:"",    
                    "s^time"=>isset($data['time'])?(string)$data['time']:"",
                    "s^order_amount"=>isset($data['order_amount'])?$data['order_amount']:"",                       
                    "s^first_order"=>isset($data['first_order'])?(string)$data['first_order']:"",
                    "s^no_of_seat_reserved"=>isset($data['seats'])?$data['seats']:"",
                    "s^image_count"=>isset($data['image_count'])?(string)$data['image_count']:"",
                    "s^bookmark_type"=>isset($data['bookmark_type'])?(string)$data['bookmark_type']:"",
                    "s^menu_item"=>isset($data['menu_item'])?(string)$data['menu_item']:"",
                    "s^description"=>isset($data['description'])?(string)$data['description']:"",
                    "s^point_redeemed"=>isset($data['point_redeemed'])?(string)$data['point_redeemed']:"",
                    "s^reffered_email"=>isset($data['reffered_email'])?(string)$data['reffered_email']:"",
                    "s^signed_in"=>(isset($data['source'])&& ($data['source']=="iOS" || $data['source']=="android"))?"normal from app":"first_time",
                    "s^check_in_with"=>isset($data['check_in_with'])?$data['check_in_with']:"", 
                    "s^refer_date"=>isset($data['refer_date'])?(string)$data['refer_date']:"",
                    "s^bookmark_date"=>isset($data['date'])?(string)$data['date']:"",
                    "s^review_type"=>isset($data['review_type'])?(string)$data['review_type']:"",
                    "s^review_date"=>isset($data['review_date'])?(string)$data['review_date']:"",
                    "s^gallery_date"=>isset($data['gallery_date'])?(string)$data['gallery_date']:"",
                    "s^reservation_date"=>isset($data['reservation_date'])?(string)$data['reservation_date']:"", 
                    "s^host_url"=>(isset($data['host_url']) && !empty($data['host_url']))?$data['host_url']:PROTOCOL.SITE_URL
                    
                    )
                )
                )
        );
        ##############
        //pr($postData);
        ##############
        
        $url = $this->netcore['apiurl']."/".$this->version[0]."/"."activity/singleactivity/".$this->netcore['clientid'];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);        
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        $info = curl_getinfo($ch);
              
        
        MUtility\MunchLogger::writeLogCleverTap(new \Exception('clevertap'), 4, "RequestData(Event):".$postData);
        MUtility\MunchLogger::writeLogCleverTap(new \Exception('clevertap'), 4, "===========================================================================================================================");
        MUtility\MunchLogger::writeLogCleverTap(new \Exception('clevertap'), 4, "ResponseData(Event):".json_encode($result));
        MUtility\MunchLogger::writeLogCleverTap(new \Exception('clevertap'), 4, "=================================================================");
        
    }
    
    public function updateProfile($data){
        
        $postData = json_encode(array( 
            "EMAIL"=>$data['email'],   
            "EARNED_POINTS"=>$data['earned_points'],
            "REDEEMED_POINTS"=>$data['redeemed_points'],
            "EARNED_DOLLAR"=>(string)$data['earned _dollar'],
            "REMAINING_POINTS"=>$data['remaining_points'],
            "REMAINING_DOLLAR"=>(string)$data['remaining_dollar'],            
        ));     

       
        $url = $this->netcore['apiurl']."/".$this->version[1]."?"."type=contact&activity=update";
        $readyData = array('apikey'=>$this->netcore['apikey'],"data"=>$postData);
        ##################
       // pr($postData);
        ##################
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);        
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($readyData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        $info = curl_getinfo($ch);
       
                  
          
        MUtility\MunchLogger::writeLogCleverTap(new \Exception('netcore'), 4, "RequestData(update profile):".$postData);
        MUtility\MunchLogger::writeLogCleverTap(new \Exception('netcore'), 4, "=================================================================");
        MUtility\MunchLogger::writeLogCleverTap(new \Exception('netcore'), 4, "ResponseData(update profile):".$result);
        MUtility\MunchLogger::writeLogCleverTap(new \Exception('netcore'), 4, "=================================================================");
    }
    
    public function restaurantStory($restaurantId) {
        $storyModel = new \Restaurant\Model\Story();
        $options = array('columns' => array('id', 'atmosphere', 'neighborhood', 'restaurant_history', 'chef_story', 'cuisine'), 'where' => array("restaurant_id" => $restaurantId), 'limit' => 1);
        $story = $storyModel->findStory($options)->toArray();
        if (!empty($story[0]['restaurant_history'])) {
            $restaurantStory = $story[0]['restaurant_history'];
        } elseif (!empty($story[0]['cuisine'])) {
            $restaurantStory = $story[0]['cuisine'];
        } elseif (!empty($story[0]['neighborhood'])) {
            $restaurantStory = $story[0]['neighborhood'];
        } elseif (!empty($story[0]['chef_story'])) {
            $restaurantStory = $story[0]['chef_story'];
        } elseif (!empty($story[0]['atmosphere'])) {
            $restaurantStory = $story[0]['atmosphere'];
        } else {
            $restaurantStory = "";
        }
        return substr($restaurantStory, 0,250)."...";
    }
     
}

?>