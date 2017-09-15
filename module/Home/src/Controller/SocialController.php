<?php

namespace Home\Controller;

use MCommons\Controller\AbstractRestfulController;
use MCommons\StaticOptions;
use Zend\Json\Json;
use MCommons\Caching;

class SocialController extends AbstractRestfulController {
    public static $totalFeed = 0;
    public static $config = array(
        'adapter' => 'Zend\Http\Client\Adapter\Curl',
        'curloptions' => array(
            CURLOPT_FOLLOWLOCATION => true
        )
    );

    public function getList() {       
        $limit = $this->getQueryParams('limit',50);
        $page = $this->getQueryParams('page',1);
        $offset = 0;
        if ($page > 0) {
            $page = ($page < 1) ? 1 : $page;
            $offset = ($page - 1) * ($limit);
        }     
      
      
        $memcache = new Caching();
        $config = $this->getServiceLocator()->get('Config');
        if ($config ['constants'] ['memcache'] && $memcache->get('social_feed')) {
            $socialFeed = $memcache->get('social_feed');
            shuffle($socialFeed);
            $records = array_slice($socialFeed,$offset,$limit);
            $records['total_feed'] = $socialFeed = $memcache->get('total_social_feed_count');
            return $records;
            
        } else {
            $SocialData = array();
            $SocialData = array_merge($SocialData,$this->getInstagramFeed(), $this->getFacebookData(),$this->getGooglePlusFeedData(), $this->getTweetsData());
            shuffle($SocialData);
            foreach ($SocialData as &$socialItem) {
                $dateTime = new \DateTime($socialItem ['published_date_time']);
                $socialItem ['published_date_time'] = $dateTime->format('d M Y H:i');
            }
            $memcache->set('social_feed', $SocialData, 60 * 60 * 1);
            $memcache->set('total_social_feed_count', self::$totalFeed, 60 * 60 * 1);
            $records = array_slice($SocialData,$offset,$limit);
            $records['total_feed'] = self::$totalFeed;
            return $records;
        }
    }

    private function getBlogsData() {
        $config = $this->getServiceLocator()->get('config');
        $rawXML = file_get_contents($config ['blog'] ['blog_url']);
        if (!empty($rawXML)) {
            $blogData = $this->extractFeedDataForblog($rawXML, $count = 2);

            if (empty($blogData)) {
                return array();
            } else {
                return $blogData;
            }
        } else {
            return array();
        }
    }

    private function getTweetsData() {
        $constants = $this->getGlobalConfig();
        $config = $this->getServiceLocator()->get('config');
        $notweets = 5;
        $connection = StaticOptions::getConnectionWithTwitterAccessToken($constants ['twitter'] ['key'], $constants ['twitter'] ['secret'], $accesstoken = "", $accesstokensecret = "");
        $data = $connection->get($config ['twitter'] ['twitterfeed_url'] . "?screen_name=" . $constants ['twitter'] ['handle'] . "&count=" . $notweets);
        if (empty($data)) {
            return array();
        }
        $tweetsData = $this->extractFeedDataForTwitter($data);
        
        if (empty($tweetsData)) {
            return array();
        } else {
            return $tweetsData;
        }
    }

    private function getFacebookData() {
        $constants = $this->getGlobalConfig();
        $config = $this->getServiceLocator()->get('config');
        if (!isset($constants ['facebook']['page_id']) || !isset($constants ['facebook']['access_token'])) {
            return array();
        }
        $uri = $config ['facebook'] ['facebook_url'] . $constants ['facebook'] ['page_id'] . "/feed?access_token=" . $constants ['facebook'] ['access_token'] . "&fields=&fields=picture,link,message,type,object_id,created_time,likes.limit(1).summary(true)";
        $client = new \Zend\Http\Client($uri, self::$config);
        $req = $client->getRequest();
        $response = $client->send($req)->getBody();
        if (empty($response)) {
            return array();
        }
        $data = Json::decode($response, Json::TYPE_ARRAY);
        $fbData = $this->extractFeedDataForFacebook($data, $count = 8);
        if (empty($fbData)) {
            return array();
        } else {
            return $fbData;
        }
    }

    private function getPintrestData() {
        $config = $this->getServiceLocator()->get('config');
        $xml = simplexml_load_file($config ['pintrest'] ['pintrest_url']);
        if (!empty($xml)) {
            $pintrestData = $this->extractFeedDataForPintrest($xml, $count = 3);

            if (empty($pintrestData)) {
                return array();
            } else {
                return $pintrestData;
            }
        } else {
            return array();
        }
    }

    private function getGooglePlusFeedData() {
        $constants = $this->getGlobalConfig();
        $config = $this->getServiceLocator()->get('config');
        $uri = $config ['google+'] ['googleplus_url'] . $constants ['google+'] ['app_id'] . '/activities/public?key=' . $constants ['google+'] ['api_key'];
        $client = new \Zend\Http\Client($uri, self::$config);
        $req = $client->getRequest();
        $response = $client->send($req)->getBody();
        if (empty($response)) {
            return array();
        }
        $data = Json::decode($response, Json::TYPE_ARRAY);
        $googlePlusData = $this->extractFeedDataForGooglePlus($data, $count = 5);

        if (empty($googlePlusData)) {
            return array();
        } else {
            return $googlePlusData;
        }
    }

    public function getInstagramFeed() {
        $constants = $this->getGlobalConfig();
        $config = $this->getServiceLocator()->get('config');

        $uri = $config ['instagram'] ['instagram_url'] . '/self/media/recent?access_token=' . $constants ['instagram'] ['access_token'];
        $client = new \Zend\Http\Client($uri, self::$config);
        $req = $client->getRequest();
        $response = $client->send($req)->getBody();

        if (empty($response)) {
            return array();
        }
        $data = Json::decode($response, Json::TYPE_ARRAY);        
        $istagramData = $this->extractFeedDataForInstagram($data, $count = 5);

        if (empty($istagramData)) {
            return array();
        } else {
            return $istagramData;
        }
    }

    public function extractFeedDataForInstagram($data, $count) {
        $config = $this->getServiceLocator()->get('config');
        $instgramDataArray = array();
        $i = 0;
        foreach ($data ['data'] as $key => $val) {
            if (isset($val ['images']['standard_resolution']) && !empty($val ['images']['standard_resolution'])) {
                $instgramDataArray [$i] ['type'] = "instagram";
                $instgramDataArray [$i] ['description'] = @$val ['caption']['text'];
                $instgramDataArray [$i] ['url'] = @$val ['link'];
                $instgramDataArray [$i] ['image_url'] = $val ['images']['standard_resolution']['url'];
                $instgramDataArray [$i] ['published_date_time'] = date("Y-m-d H:i:s", $val ['created_time']);
                $instgramDataArray [$i] ['retweet_url'] = $val ['link'];
                $instgramDataArray[$i]['total_likes'] = $val['likes']['count'];
                $i ++;
            }
        }
        self::$totalFeed = self::$totalFeed+count($instgramDataArray);
        return $instgramDataArray;
    }

    public function extractFeedDataForblog($data, $count) {
        $data = str_replace('<![CDATA[', "", $data);
        $data = str_replace(']]>', "", $data);
        $data = simplexml_load_string($data);
        $blogDataArray = array();
        $blog_as_array = (array) $data;
        $i = 0;
        if (isset($blog_as_array['channel'])) {
            foreach ($blog_as_array ['channel'] as $key => $blog) {
                if ($i < $count) {
                    $blog_array = (array) $blog;
                    if (isset($blog_array ['title']) && !empty($blog_array ['title'])) {
                        $blogDataArray [$i] ['type'] = 'blog';
                        $blogDataArray [$i] ['description'] = $blog_array ['title'];
                        $blogDataArray [$i] ['url'] = $blog_array ['link'];
                        $description = $blog_array ['description'];
                        $description = (array) $description;
                        $image = (array) $description['img']['src'];
                        $blogDataArray [$i] ['image_url'] = str_replace('http', 'https', $image[0]);
                        $blogDataArray [$i] ['published_date_time'] = $blog_array ['pubDate'];
                        $i ++;
                    }
                } else {
                    break;
                }
            }
        }
        return $blogDataArray;
    }

    public function extractFeedDataForFacebook($data, $count) {
        $config = $this->getServiceLocator()->get('config');
        $fbDataArray = array();
        $i = 0;
        foreach ($data ['data'] as $key => $val) {
            $id = explode("_", $val['id']);
            if ($id[0] == '491596394245644') {

                if (isset($val['picture']) && !empty($val['picture'])) {
                    $fbDataArray [$i] ['type'] = "facebook";
                    $fbDataArray [$i] ['description'] = @$val ['message'];
                    $fbDataArray [$i] ['url'] = @$val ['link'];
                    if (isset($val ['picture'])) {
                        $fbDataArray [$i] ['image_url'] = $val['picture'];
                    }
                    $fbDataArray [$i] ['published_date_time'] = $val ['created_time'];
                    $fbDataArray [$i] ['retweet_url'] = $config ['facebook'] ['facebookshare_url'] . "?u=" . $val ['link'];
                    $fbDataArray[$i]['total_likes'] = $val['likes']['summary']['total_count'];
                    $i ++;
                }
            }
        }
        self::$totalFeed = self::$totalFeed+count($fbDataArray);
        return $fbDataArray;
    }

    public function extractFeedDataForTwitter($data) {
        $config = $this->getServiceLocator()->get('config');
        $tweetsDataArray = array();
        
        foreach ($data as $key => $val) {
            
            $tweetsDataArray [$key] ['type'] = "twitter";
            $tweetsDataArray [$key] ['description'] = $val->text;
            if (isset($val->entities->media[0]->media_url_https) && !empty($val->entities->media[0]->media_url_https)) {
                $tweetsDataArray [$key] ['url'] = $config ['twitter'] ['twitter_url'] . $val->user->screen_name;
                $tweetsDataArray [$key] ['image_url'] = $val->entities->media[0]->media_url_https;
            }
            $tweetsDataArray [$key] ['published_date_time'] = $val->created_at;
            $tweetsDataArray [$key] ['retweet_url'] = $config ['twitter'] ['retweet_url'] . "?tweet_id=" . $val->id_str;
            $tweetsDataArray[$key]['total_likes'] = $val->favorite_count;
        }
        self::$totalFeed = self::$totalFeed+count($tweetsDataArray);
       
        return $tweetsDataArray;
    }

    public function extractFeedDataForPintrest($data, $count) {
        $config = $this->getServiceLocator()->get('config');
        $pinDataArray = array();
        $pinData = (array) $data;
        $pinData = (array) $pinData ['channel'];
        $i = 0;
        foreach ($pinData ['item'] as $key => $pin) {          
                $pinArray = (array) $pin;
                               
                if (isset($pinArray ['description']) && !empty($pinArray ['description'])) {
                    preg_match_all('/< *img[^>]*src *= *["\']?([^"\']*)/i',$pinArray ['description'], $matches);
                    $image_url = $matches[1][0];
                    
                    if (isset($image_url) && !empty($image_url)) {
                        $pinDataArray [$i] ['type'] = 'pintrest';
                        $pinDataArray [$i] ['description'] = strip_tags($pinArray ['description']);
                        $pinDataArray [$i] ['url'] = $pinArray ['link'];
                        $pinDataArray [$i] ['image_url'] = $image_url;
                        $pinDataArray [$i] ['published_date_time'] = $pinArray ['pubDate'];
                        $pinDataArray [$i] ['retweet_url'] = $config ['pintrest'] ['repin_url'] . "?url=" . urlencode($pinArray ['link']) . "&media=" . urlencode($image_url) . "&description=" . urlencode(strip_tags($pinArray ['description']));
                        $pinDataArray[$i]['total_likes'] = 0;
                        $i ++;
                    }
                }
          
        }
        self::$totalFeed = self::$totalFeed+count($pinDataArray);
        return $pinDataArray;
    }

    public function extractFeedDataForGooglePlus($data, $count) {
        $config = $this->getServiceLocator()->get('config');
        $googleDataArray = array();
        $i = 0;
        foreach ($data ['items'] as $key => $val) {

            if (isset($val ['object'] ['attachments'] [0] ['image'] ['url']) && !empty($val ['object'] ['attachments'] [0] ['image'] ['url'])) {
                $googleDataArray [$i] ['type'] = "google+";
                $googleDataArray [$i] ['description'] = strip_tags($val ['object'] ['content']);
                $googleDataArray [$i] ['url'] = $val ['url'];
                if (isset($val ['object'] ['attachments'] [0] ['image'])) {
                    $googleDataArray [$i] ['image_url'] = $val ['object'] ['attachments'] [0] ['image'] ['url'];
                } else {
                    $googleDataArray [$i] ['image_url'] = "";
                }

                $googleDataArray [$i] ['published_date_time'] = $val ['published'];
                $googleDataArray [$i] ['retweet_url'] = $config ['google+'] ['googleshare_url'] . "?url=" . $val ['url'];
                $googleDataArray[$i]['total_likes'] = $val['object']['plusoners']['totalItems'];
                $i ++;
            }
        }
        self::$totalFeed = self::$totalFeed+count($googleDataArray);
        return $googleDataArray;
    }

}
