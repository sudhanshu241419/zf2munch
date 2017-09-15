<?php

/*
 * Use this controller for testing purpose only.
 */

namespace Search\Controller;

use MCommons\StaticOptions;
use MCommons\Controller\AbstractRestfulController;
use Zend\Http\Client;

//require '/home/manoj/workspace/munch/vendor/autoload.php';

class TestController extends AbstractRestfulController {
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
    private $version = array("v1","apiv2");
    private $netcore =array(
          'apiurl'=>'https://api.netcoresmartech.com',
          'apikey'=>'58b5dc3ec938fb539bb8d6967bacbc28',          
          'clientid'=>'ADGMOT35CHFLVDHBJNIG50K968IHHKCDLJANN9CGBV4A433SLPDG'
        );

    public function getList() {
               $story = html_entity_decode(htmlspecialchars_decode("Opened in 1988, El Aztecas Tex-Mex cuisine ranges from great margaritas people flock here to try, to a dish called Cochinita Pibil.  What is cochinita Pibil you ask? A slow baked pork in achote, orange and lemon juice covered with banana leaves an...", ENT_QUOTES));
        $postData ='{
	"activityid": 111,
	"identity": "sudmukesh@mailinator.com",
	"activity_source": "CUS",
	"activity_params": [{
		"s^email": "sudmukesh@mailinator.com",
		"s^restaurant_dine_more": "no",
		"s^restaurant_name": "El Azteca",
		"s^is_register": "yes",
		"s^restaurant_id": "58374",
		"s^delivery_enabled": "",
		"s^takeout_enabled": "",
		"s^reservation_enabled": "",
		"s^user_dine_more": "no",
		"s^point_earned": "2",
		"s^user_id": "2248",
		"s^last_name": "",
		"s^first_name": "sud",
		"s^date": "2017-06-13 10:23:51",
		"s^restaurant_story": '.$story.',
        "s^orderid": "",
		"s^paid_with_point": "0",
		"s^paid_with_card": "0",
		"s^order_date": "",
		"s^order_time": "",
		"s^order_type": "",
		"s^type": "",
		"s^deal_offer": "",
		"s^time": "",
		"s^order_amount": "",
		"s^first_order": "",
		"s^no_of_seat_reserved": "",
		"s^image_count": "",
		"s^bookmark_type": "",
		"s^menu_item": "",
		"s^description": "",
		"s^point_redeemed": "",
		"s^reffered_email": "",
		"s^signed_in": "first_time",
		"s^check_in_with": "checkin",
		"s^refer_date": "",
		"s^bookmark_date": "2017-06-13 10:23:51",
		"s^review_type": "",
		"s^review_date": "",
		"s^gallery_date": "",
		"s^reservation_date": ""
	}]
}';
        ##############
        pr($postData);
        ##############
        
        $url = $this->netcore['apiurl']."/".$this->version[0]."/"."activity/singleactivity/".$this->netcore['clientid'];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);        
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        pr($result);
        $info = curl_getinfo($ch);
        pr($info);
    } 
    
    

}

