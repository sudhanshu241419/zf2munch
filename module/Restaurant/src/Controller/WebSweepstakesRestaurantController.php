<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;
use Restaurant\Model\Tags;


class WebSweepstakesRestaurantController extends AbstractRestfulController {
	public function getList() {
		$tags = new Tags();
        $restaurantData = $tags->getSweestakesTagsRestaurant();
        $this->menuAndImageLink($restaurantData);
        //pr($restaurantData,1);
        return $restaurantData;
	}
    
    public function menuAndImageLink(&$restaurantData){
        if(!empty($restaurantData)){
             $config = $this->getServiceLocator()->get('Config');  
            foreach($restaurantData as $key => $val){
                //pr($val);
                $restaurantData[$key]['order_online'] = (($restaurantData[$key]['delivery'] || $restaurantData[$key]['takeout']) && !$restaurantData[$key]['menu_without_price'] && $restaurantData[$key]['accept_cc_phone'])?(int)1:(int)0;
                $restaurantData[$key]['reservation_online'] = (int)$restaurantData[$key]['reservations'];
                $restaurantData[$key]['menu_link']=PROTOCOL.SITE_URL."restaurants/".$val['restaurant_name']."/".$val['restaurant_id']."/menu";
                $restaurantData[$key]['restaurant_image_name']=$config['constants']['protocol']."://".$config['constants']['imagehost'].'munch_images/'.strtolower($val['rest_code'])."/".$val['restaurant_image_name'];
                unset($restaurantData[$key]['reservations'],$restaurantData[$key]['menu_without_price'],$restaurantData[$key]['accept_cc_phone'],$restaurantData[$key]['tags_id'],$restaurantData[$key]['tag_name'],$restaurantData[$key]['rest_code'],$restaurantData[$key]['delivery'],$restaurantData[$key]['takeout']);
            }
        }
    }

}
