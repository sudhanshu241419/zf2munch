<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;

class DinenmoreDealController extends AbstractRestfulController {

    public function get($id) {
        
            $dinenmoreDeals['dine_more'] = array(                
                array('description' => 'Receive 1 free desert item, in-restaurant, per visit',
                    'image_2x' => WEB_URL.'img/recieve@2x.png',
                    'image_3x' => WEB_URL.'img/recieve@3x.png',
                   ),
                array(
                    'description' => '15% Off Your First Delivery Order',
                    'image_2x' => WEB_URL.'img/per_off@2x.png',
                    'image_3x' => WEB_URL.'img/per_off@3x.png',
                ),
                array('description' => '2 Free Glasses of Wine with Reservation',
                    'image_2x' => WEB_URL.'img/glass@2x.png',
                    'image_3x' => WEB_URL.'img/glass@3x.png',
                    ),
                array('description'=>'',
                    'image_2x' => WEB_URL.'img/Flash_offer@2x.png',
                    'image_3x' => WEB_URL.'img/Flash_Offer@3x.png',
                    ),
                array('description'=>'',
                    'image_2x' => WEB_URL.'img/amazon@2x.png',
                    'image_3x' => WEB_URL.'img/amazon@3x.png',
                    ),
                array('description'=>'',
                    'image_2x' => WEB_URL.'img/Broadway.com@2x.png',
                    'image_3x' => WEB_URL.'img/Broadway.com@3x.png',
                    ),
                array('description'=>'',
                    'image_2x' => WEB_URL.'img/Spotify@2x.png',
                    'image_3x' => WEB_URL.'img/Spotify@3x.png',
                    ),
                array('description'=>'',
                    'image_2x' => WEB_URL.'img/Xbox@2x.png',
                    'image_3x' => WEB_URL.'img/Xbox@3x.png',
                    ),
                array('description'=>'',
                    'image_2x' => WEB_URL.'img/Netflix@2x.png',
                    'image_3x' => WEB_URL.'img/Netflix@3x.png',
                    ),
                );
        return $dinenmoreDeals;
    }
}