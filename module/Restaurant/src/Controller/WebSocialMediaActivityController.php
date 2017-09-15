<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;
use MCommons\StaticOptions;
use MCommons\CommonFunctions;
/*
 *  Implemented date 11-05-2016 
 *  This api work to get social media activity counter (like,rating,checkin)
 *  Parameter restaurantId  
 */
class WebSocialMediaActivityController extends AbstractRestfulController {

    public function get($restaurantId) {
        try {
            $config = $this->getServiceLocator()->get('Config');
            $accessToken=$config['constants']['facebook']['access_token'];
            $restaurantModal=new \Restaurant\Model\RestaurantSocialMediaActivity();
            $socialUrls=$restaurantModal->getResSocialUrls($restaurantId);
            if(!empty($socialUrls)){
               $fbUrl=trim(isset($socialUrls['fb_like_url']) && $socialUrls['fb_like_url']!=''?$socialUrls['fb_like_url']:"");
               $fourSquUrl=trim(isset($socialUrls['foursquare_rating_url']) && $socialUrls['foursquare_rating_url']!=''?$socialUrls['foursquare_rating_url']:"");
               $data=[];
               //find social activity from facebook
               if($fbUrl!=''){
               $data['fburl']=$fbUrl;    
               $fbSourceUrl="https://graph.facebook.com/v2.3/?id=$fbUrl&access_token=$accessToken&fields=checkins,likes,were_here_count";
               //pr($fbSourceUrl,1);
               $fbResponse = CommonFunctions::curlRequest($fbSourceUrl,'GET');
               if($fbResponse!=''){
                   $data['likes']=isset($fbResponse['likes'])?$fbResponse['likes']:0;
                   $data['checkins']=isset($fbResponse['were_here_count'])?$fbResponse['were_here_count']:0;
               }else{
                   $data['likes']=0;
                   $data['checkins']=0;
               }
               }else{
                $data['fbUrl']='';
                $data['likes']=0;
                $data['checkins']=0;
               }
               //find social activity from four squere
               if($fourSquUrl!=''){
               $data['foursquurl']=$fourSquUrl;    
               $fourSquUrlCode=substr(strrchr($fourSquUrl, '/'), 1);
               $fourSourceUrl="https://api.foursquare.com/v2/venues/$fourSquUrlCode?oauth_token=ECC0YK4X0DBALF20ZCW5A1FJOVCNJ14XVAATJQPDVIEKI03R&v=20160505";
               $fourResponse = CommonFunctions::curlRequest($fourSourceUrl,'GET');
               if($fourResponse!=''){
                   $data['rating']=isset($fourResponse['response']['venue']['rating'])?$fourResponse['response']['venue']['rating']:0;
               }else{
                   $data['rating']=0;
               }
               }else{
                   $data['foursquurl']='';
                   $data['rating']=0;
               }
              
            }else{
                $data['foursquurl']='';
                $data['rating']=0;
                $data['fbUrl']='';
                $data['likes']=0;
                $data['checkins']=0;
            }  
            return $data;
        } catch (\Exception $e) {
           \MUtility\MunchLogger::writeLog($e, 1,'Something Went Wrong On Social Activity Api');
           throw new \Exception($e->getMessage(),400);
        }
    }
}