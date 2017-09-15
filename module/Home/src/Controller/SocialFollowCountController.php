<?php

namespace Home\Controller;

use MCommons\Controller\AbstractRestfulController;
use MCommons\StaticOptions;
use Zend\Json\Json;
use MCommons\Caching;

class SocialFollowCountController extends AbstractRestfulController {

    public static $config = array(
        'adapter' => 'Zend\Http\Client\Adapter\Curl',
        'curloptions' => array(
            CURLOPT_FOLLOWLOCATION => true
        )
    );

    public function getList() {
        $memCached = new Caching();
        //$memCached = $this->getServiceLocator()->get('memcached');
        $socialFollow = array();
       $this->getFacebookLikesCount();
        $config = $this->getServiceLocator()->get('Config');
        $values = $memCached->get('footerSocialCount');
        if ($config['constants']['memcache'] && $values) {
            return  $values ;         
        } else {
            //$socialFollow = array_merge (  $this->getTweetsCount (), $this->getFacebookLikesCount (),$this->getGooglePlusFollowCount(),$this->getPintressFollowersCount(),$this->getInstagramFollowersCount());
            $socialFollow = array_merge($this->getTweetsCount(), $this->getFacebookLikesCount(), $this->getGooglePlusFollowCount(), $this->getPintressFollowersCount());
            $flag = $memCached->set('footerSocialCount', $socialFollow,60*60*24);
            return $socialFollow;
        }
    }

    private function getTweetsCount() {
        $constants = $this->getGlobalConfig();
        $config = $this->getServiceLocator()->get('config');
        $notweets = 3;
        $connection = StaticOptions::getConnectionWithTwitterAccessToken($constants ['twitter'] ['key'], $constants ['twitter'] ['secret'], $accesstoken = "", $accesstokensecret = "");
        $data = $connection->get($config ['twitter'] ['twitterfeed_url'] . "?screen_name=" . $constants ['twitter'] ['handle'] . "&count=" . $notweets);
        $followCount = array();
        $followCount['twitter_followers_count'] = "0";
        if (!empty($data)) {
            foreach ($data as $key => $val) {
                if ($val->user) {
                    $count = ($val->user->followers_count) ? $val->user->followers_count : 0;
                    $followCount['twitter_followers_count'] = "$count";
                }
            }
        }

        return $followCount;
    }

    private function getFacebookLikesCount() {
        $config = $this->getServiceLocator()->get('config');
        $constants = $this->getGlobalConfig();
        $likesCount = array();
        $likesCount['facebook_likes'] = "0";
        
        if (!isset($constants ['facebook']['page_id'])) {
            return $likesCount;
        }
              
        $uri = $config ['facebook'] ['facebook_url'] ."/".$constants ['facebook'] ['page_id'] . "?access_token=" . $constants ['facebook']['access_token']."&fields=likes";
       
        $client = new \Zend\Http\Client($uri, self::$config);
  
        
        $req = $client->getRequest();
        $response = $client->send($req)->getBody();
       
        if (empty($response)) {
            return $likesCount;
        }
        $data = Json::decode($response, Json::TYPE_ARRAY);
       
        if (!empty($data)) {          
            $likecounts = (isset($data['likes']) && $data['likes']>0)?$data['likes']:0;
            $likesCount['facebook_likes'] = "$likecounts";
        }
        
        return $likesCount;
    }

    private function getGooglePlusFollowCount() {
        $constants = $this->getGlobalConfig();
        $config = $this->getServiceLocator()->get('config');
        $uri = $config ['google+'] ['googleplus_url'] . $constants ['google+'] ['app_id'] . '?key=' . $constants ['google+'] ['api_key'];
        $client = new \Zend\Http\Client($uri, self::$config);
        $req = $client->getRequest();
        $response = $client->send($req)->getBody();
        $followCount['googleplus_followers'] = "0";
        if (empty($response)) {
            return $followCount;
        }
        $data = Json::decode($response, Json::TYPE_ARRAY);
        if (!empty($data)) {
            $googleFollowers = $data['circledByCount'];
            $followCount['googleplus_followers'] = "$googleFollowers";
        }
        return $followCount;
    }

    private function getPintressFollowersCount() {
        $followCount = array();
        $followCount['pintress_follow_count'] = "0";
        $metas = get_meta_tags('http://pinterest.com/munchado/');
        if ($metas['pinterestapp:followers']) {
            $followCount['pintress_follow_count'] = $metas['pinterestapp:followers'];
        }
        return $followCount;
    }

    private function getInstagramFollowersCount() {
        $constants = $this->getGlobalConfig();
        $config = $this->getServiceLocator()->get('config');
        $followCount = array();
        $followCount['instagram_follow_count'] = "0";
        $url = $constants['instagram']['instagram_url'] . '/' . $config['instagram']['cient_id'] . '?access_token=' . $config['istagram']['access_token'];
        $api_response = file_get_contents($url);
        if ($api_response) {
            $record = json_decode($api_response);
            $followCount['instagram_follow_count'] = $record->data->counts->follows;
        }
        return $followCount;
    }

}
