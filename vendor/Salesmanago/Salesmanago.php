<?php

use MCommons\StaticOptions;
use Zend\Json\Json;

class Salesmanago {

    private $salesmanagoClientId = 'zr2p0l2ggbqp2t4v';
    private $salesmanagoApiKey = 'j2q8qp4fbp9qf2b8p49fb';
    private $salesmanagoApiSecret = 'kcc5syfpbkec15k3srpf3m5oauht0d5v';
    private $salesmanagoEndpoint = 'www.salesmanago.pl';

    const OWNER_EMAIL = "bmukhia@aydigital.com";

    public static $config = array(
        'adapter' => 'Zend\Http\Client\Adapter\Curl',
        'curloptions' => array(
            CURLOPT_FOLLOWLOCATION => true
        )
    );

    public static function curlRequest($url = false, $data, $method = false) {
        $request = new \Zend\Http\Request();
        $request->setUri($url);
        $request->setMethod('POST');
        $request->getPost()->fromArray($data);

        $client = new \Zend\Http\Client();

        $client->setEncType(\Zend\Http\Client::ENC_FORMDATA);
        $response = $client->dispatch($request);

        if ($response->isSuccess()) {
            echo "the post worked!";
        } else {
            echo "the post failed";
        }
    }

    public function postToSalesmanago($url, $data, $type) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data)
                )
        );
        return curl_exec($ch);
    }

    public function registerOnSalesmanago($data = array(), $envirnment = false) {
        $name = isset($data['name']) ? $data['name'] : '';
        $email = $data['email'];
        $phone = isset($data['phone']) ? $data['phone'] : "";
        $dineMore = isset($data['dine_more']) ? $data['dine_more'] : '';
        $restaurantId = isset($data['restaurant_id']) ? $data['restaurant_id'] : '';
        $restaurantName = $data['restaurant_name'];
        $sl = Staticoptions::getServiceLocator();
        $config = $sl->get('Config');
        $webUrl = PROTOCOL . $config['constants']['web_url'];
        $urlRestName = ($dineMore === 'Yes') ? str_replace(" ", "-", strtolower(trim($data['restaurant_name']))) : '';
        $restaurantUrl = ($dineMore === 'Yes') ? $webUrl . "/loginRedirect?url=restaurants/" . $urlRestName . "/" . $data['restaurant_id'] : "";
        $countags = count($data['tags']);
        $data['tags'][$countags] = $envirnment;
        $data['tags'][$countags + 1] = (isset($data['user_source']) && $data['user_source'] == 'sms') ? 'sms' : 'ws';
        $tags = $data['tags'];
        $earnpoint = isset($data['point']) ? $data['point'] : '';
        $totalpoint = isset($data['totalpoint']) ? $data['totalpoint'] : '';
        //$redeempoint = isset($data['redeempoint'])?$data['redeempoint']:'';
        $story = $this->restaurantStory($restaurantId);
        $data = array(
            'clientId' => $this->salesmanagoClientId,
            'apiKey' => $this->salesmanagoApiKey,
            'requestTime' => time(),
            'sha' => sha1($this->salesmanagoApiKey . $this->salesmanagoClientId . $this->salesmanagoApiSecret),
            'contact' => array(
                'company' => "",
                'email' => $email,
                'name' => $name,
                'phone' => $phone,
                'state' => 'CUSTOMER',
            ),
            'owner' => self::OWNER_EMAIL, //PLEASE ENTER CONTACTS OWNER'S EMAIL ADDRESS HERE
            'tags' => $tags,
            'removeTags' => array("Tag_to_remove"),
            'properties' => array(
                'page' => 'registration',
                'dine_and_more' => $dineMore,
                'restaurant_id' => $restaurantId,
                'restaurant_url' => $restaurantUrl,
                'earned_point' => $earnpoint,
                'redeemed_point' => 0,
                'total_points' => $totalpoint,
                'restaurant_name' => isset($data['restaurant_name']) ? $data['restaurant_name'] : '',
                'detail9' => $data['email'],
                'detail10' => (isset($data['password']) && !empty($data['password'])) ? $data['password'] : "",
                'detail11' => (isset($data['user_source']) && $data['user_source'] == 'sms') ? 'sms' : 'ws',
                'detail12' => $webUrl . "/",
                'detail13' => $story,
            ),
            'lang' => 'EN',
            'useApiDoubleOptIn' => false,
            'forceOptIn' => true,
            'forceOptOut' => false,
            'forcePhoneOptIn' => true,
            'forcePhoneOptOut' => false
        );
        $json = json_encode($data, 1);
        
        ///pr('http://' . $this->salesmanagoEndpoint . '/api/contact/upsert');
        
        $result = $this->postToSalesmanago('http://' . $this->salesmanagoEndpoint . '/api/contact/upsert', $json, 'POST');
        
        $this->registrationEvent(array('restaurant_id' => $restaurantId, 'restaurant_name' => $restaurantName, 'email' => $email, 'weburl' => $webUrl, 'restaurant_url' => $restaurantUrl, 'story' => $story));
        MUtility\MunchLogger::writeLog(new \Exception('SalesManago-Response'), 4, json_encode($result));
        MUtility\MunchLogger::writeLogSalesmanago(new \Exception('SalesManago-Response'), 4, $json);
    }

    public function updatePhoneOnSalesmanago($data = array()) {
        $email = $data['email'];
        $phone = isset($data['phone']) ? $data['phone'] : "";

        $data = array(
            'clientId' => $this->salesmanagoClientId,
            'apiKey' => $this->salesmanagoApiKey,
            'requestTime' => time(),
            'sha' => sha1($this->salesmanagoApiKey . $this->salesmanagoClientId . $this->salesmanagoApiSecret),
            'contact' => array(
                'email' => $email,
            ),
            'owner' => self::OWNER_EMAIL,
            'properties' => array('phone' => $phone)
        );
        $json = json_encode($data);
        $result = $this->postToSalesmanago('http://' . $this->salesmanagoEndpoint . '/api/contact/upsert', $json, 'POST');
        MUtility\MunchLogger::writeLog(new \Exception('SalesManago-Response'), 4, json_encode($result));
        MUtility\MunchLogger::writeLogSalesmanago(new \Exception('SalesManago-Response'), 4, $json);
    }

    public function earnPointOnSalesmanago($data = array()) {
        $email = isset($data['email']) ? $data['email'] : '';
        $earnpoint = isset($data['point']) ? $data['point'] : 0;
        $totalpoint = isset($data['totalpoint']) ? $data['totalpoint'] : 0;
        $dollar = isset($data['earned_dollar']) ? $data['earned_dollar'] : 0;
        $redeemedPoint = isset($data['redeemed_point']) ? $data['redeemed_point'] : 0;
        $properties = array();

        if (isset($data['dine_more'])) {
            $properties = array('earned_point' => $totalpoint, 'total_points' => $earnpoint, 'redeemed_point' => $redeemedPoint, 'dine_and_more' => $data['dine_more'], 'earned_dollar' => $dollar);
        } else {
            $properties = array('earned_point' => $totalpoint, 'total_points' => $earnpoint, 'redeemed_point' => $redeemedPoint, 'earned_dollar' => $dollar);
        }

        if (isset($data['changeRestaurantName']) && $data['changeRestaurantName'] == true) {
            $properties = array('restaurant_name' => $data['restaurant_name'], 'restaurant_url' => $data['restaurant_url'], 'detail12' => $data['web_url']);
        }

        $data = array(
            'clientId' => $this->salesmanagoClientId,
            'apiKey' => $this->salesmanagoApiKey,
            'requestTime' => time(),
            'sha' => sha1($this->salesmanagoApiKey . $this->salesmanagoClientId . $this->salesmanagoApiSecret),
            'contact' => array(
                'email' => $email,
            ),
            'owner' => self::OWNER_EMAIL,
            'properties' => $properties
        );
        $json = json_encode($data);
        $result = $this->postToSalesmanago('http://' . $this->salesmanagoEndpoint . '/api/contact/upsert', $json, 'POST');
        MUtility\MunchLogger::writeLog(new \Exception('SalesManago-Response'), 4, json_encode($result));
        MUtility\MunchLogger::writeLogSalesmanago(new \Exception('SalesManago-Response'), 4, $json);
    }

    public function redeemedPointOnSalesmanago($data = array()) {
        $email = $data['email'];
        $redeempoint = $data['point'];
        $totalpoint = $data['totalpoint'];

        $data = array(
            'clientId' => $this->salesmanagoClientId,
            'apiKey' => $this->salesmanagoApiKey,
            'requestTime' => time(),
            'sha' => sha1($this->salesmanagoApiKey . $this->salesmanagoClientId . $this->salesmanagoApiSecret),
            'contact' => array(
                'email' => $email,
            ),
            'owner' => self::OWNER_EMAIL,
            'properties' => array('redeemed_point' => $redeempoint, 'total_points' => $totalpoint)
        );
        $json = json_encode($data);
        $result = $this->postToSalesmanago('http://' . $this->salesmanagoEndpoint . '/api/contact/upsert', $json, 'POST');
        MUtility\MunchLogger::writeLog(new \Exception('SalesManago-Response'), 4, json_encode($result));
        MUtility\MunchLogger::writeLogSalesmanago(new \Exception('SalesManago-Response'), 4, $json);
    }

    public function eventsOnSalesmanago($data = array()) {
        $email = $data['email'];
        $description = isset($data['description']) ? $data['description'] : "";
        $restaurantName = isset($data['restaurant_name']) ? $data['restaurant_name'] : "";
        $location = isset($data['location']) ? $data['location'] : "";
        $value = isset($data['value']) ? $data['value'] : "";
        $contactExtEventType = isset($data['contact_ext_event_type']) ? $data['contact_ext_event_type'] : "";
        //$detail1 = isset($data['detail1'])?$data['detail1']:"";
        $restaurantId = isset($data['restaurant_id']) ? $data['restaurant_id'] : "";
        $detail2 = "";
        $webUrl = "";
        $restaurantUrl = "";
        if (isset($data['restaurant_name']) && !empty($data['restaurant_name'])) {
            $sl = Staticoptions::getServiceLocator();
            $config = $sl->get('Config');
            $webUrl = PROTOCOL . $config['constants']['web_url'];
            $urlRestName = str_replace(" ", "-", strtolower(trim($restaurantName)));
            $restaurantUrl = $webUrl . "/loginRedirect?url=restaurants/" . $urlRestName . "/" . $restaurantId;
            //$detail2 = $restaurantUrl;
        }

        $externalId = isset($data['externalId']) ? $data['externalId'] : "";
        $dt = new \DateTime("NOW");
        if (isset($data['story'])) {
            $story = substr($data['story'],0,250)."...";
        } else {
            $story = $this->restaurantStory($restaurantId);
        }
        $salesMongoData = array(
            'clientId' => $this->salesmanagoClientId,
            'apiKey' => $this->salesmanagoApiKey,
            'requestTime' => time(),
            'sha' => sha1($this->salesmanagoApiKey . $this->salesmanagoClientId . $this->salesmanagoApiSecret),
            'owner' => self::OWNER_EMAIL,
            //'tags' => isset($data['tags'])?$data['tags']:"",
            'email' => $email,
            'contactEvent' => Array
                (
                'date' => $dt->format('c'),
                'description' => $description,
                'products' => $restaurantName,
                'location' => $location,
                'value' => $value,
                'contactExtEventType' => $contactExtEventType,
                'detail1' => $restaurantId,
                'detail2' => $restaurantUrl,
                'detail8' => $webUrl . "/",
                'externalId' => $externalId,
                'detail15' => $story
            ),
        );
        //pr($salesMongoData,1);
        $json = json_encode($salesMongoData);
        $result = $this->postToSalesmanago('http://www.salesmanago.pl/api/contact/addContactExtEvent', $json, 'POST');
        MUtility\MunchLogger::writeLog(new \Exception('SalesManago-Response'), 4, json_encode($result));
        MUtility\MunchLogger::writeLogSalesmanago(new \Exception('SalesManago-Response'), 4, $json);
    }

    public function customeDetailSalesmanago($data = array()) {
        $restaurantName = isset($data['restaurant_name']) ? $data['restaurant_name'] : "";
        $restaurantId = isset($data['restaurant_id']) ? $data['restaurant_id'] : "";
        $dineMore = isset($data['dine_more']) ? $data['dine_more'] : "";
        $webUrl = '';
        $restaurantUrl = '';
        if (isset($data['restaurant_name']) && !empty($data['restaurant_name'])) {
            $sl = Staticoptions::getServiceLocator();
            $config = $sl->get('Config');
            $webUrl = PROTOCOL . $config['constants']['web_url'];
            $urlRestName = str_replace(" ", "-", strtolower(trim($restaurantName)));
            $restaurantUrl = $webUrl . "/loginRedirect?url=restaurants/" . $urlRestName . "/" . $restaurantId;
        }
        $story = isset($data['story']) ? $data['story'] : '';

        $data = array(
            'clientId' => $this->salesmanagoClientId,
            'apiKey' => $this->salesmanagoApiKey,
            'requestTime' => time(),
            'sha' => sha1($this->salesmanagoApiKey . $this->salesmanagoClientId . $this->salesmanagoApiSecret),
            'contact' => array(
                'email' => $data['email'],
            ),
            'owner' => self::OWNER_EMAIL,
            'properties' => array('restaurant_id' => $restaurantId, 'restaurant_url' => $restaurantUrl, 'detail12' => $webUrl . "/", 'restaurant_name' => $restaurantName, 'dine_and_more' => $dineMore, 'detail13' => $story),
            'tags' => $data['tags']
        );
        $json = json_encode($data);
        $result = $this->postToSalesmanago('http://' . $this->salesmanagoEndpoint . '/api/contact/upsert', $json, 'POST');
        MUtility\MunchLogger::writeLog(new \Exception('SalesManago-Response'), 4, json_encode($result));
        MUtility\MunchLogger::writeLogSalesmanago(new \Exception('SalesManago-Response'), 4, $json);
    }

    public function removeTagsSalesmanago($data = array()) {

        $salesManagoData = array(
            'clientId' => $this->salesmanagoClientId,
            'apiKey' => $this->salesmanagoApiKey,
            'email' => $data['email'],
            'contactId' => null,
            'requestTime' => time(),
            'sha' => sha1($this->salesmanagoApiKey . $this->salesmanagoClientId . $this->salesmanagoApiSecret),
            'contact' => array(
                'email' => $data['email'],
            ),
            'owner' => self::OWNER_EMAIL,
            'properties' => array('restaurant_id' => '', 'restaurant_url' => '', 'restaurant_name' => '', 'dine_and_more' => "No"),
            'removeTags' => array('Dine_and_More', $data['restaurant_name']),
            'tags' => array(),
                //'lang' => 'EN',
                //'useApiDoubleOptIn' => true,
                //'forceOptIn' => true,
                //'forceOptOut' => false,
                //'forcePhoneOptIn' => true,
                //'forcePhoneOptOut' => false
        );

        $json = json_encode($salesManagoData);

        $result = $this->postToSalesmanago('http://' . $this->salesmanagoEndpoint . '/api/contact/update', $json, 'POST');
        MUtility\MunchLogger::writeLog(new \Exception('SalesManago-Response'), 4, json_encode($result));
        MUtility\MunchLogger::writeLogSalesmanago(new \Exception('SalesManago-Response'), 4, $json);
    }

    public function updateHostUrlTest($emails) {
        $webUrl = "https://munchado.com/";
        $contact = [];
        foreach ($emails as $key => $value) {
            if (!empty($value['email'])) {

                //$story = $this->restaurantStory($value['restaurant_id']);
                //$urlRestName = str_replace(" ", "-", strtolower(trim($value['restaurant_name'])));
                //$restaurantUrl = $webUrl . "loginRedirect?url=restaurants/" . $urlRestName . "/" . $value['restaurant_id'];
                $contact[] = array("contact" => array('email' => $value['email']), 'properties' => array('detail12' => $webUrl));
                //$contact[] = array("contact" => array('email' => $value['email']), 'properties' => array('detail12' => $webUrl, 'restaurant_name' => $value['restaurant_name'], 'restaurant_id' => $value['restaurant_id'], 'restaurant_url' => $restaurantUrl, 'detail13' => $story));
            }
        }
        if (!empty($contact)) {
            $data = array(
                'clientId' => $this->salesmanagoClientId,
                'apiKey' => $this->salesmanagoApiKey,
                'requestTime' => time(),
                'sha' => sha1($this->salesmanagoApiKey . $this->salesmanagoClientId . $this->salesmanagoApiSecret),
                'owner' => self::OWNER_EMAIL,
                'upsertDetails' => $contact,
            );
            $json = json_encode($data);

            $result = $this->postToSalesmanago('http://' . $this->salesmanagoEndpoint . '/api/contact/batchupsert', $json, 'POST');
            //MUtility\MunchLogger::writeLog(new \Exception('SalesManago-Response'), 4, json_encode($result));
            //MUtility\MunchLogger::writeLogSalesmanago(new \Exception('SalesManago-Response'), 4, $json);
        }
    }

    public function updateHostUrl($emails) {
        $webUrl = '';
        $sl = Staticoptions::getServiceLocator();
        $config = $sl->get('Config');
        $webUrl = PROTOCOL . $config['constants']['web_url'] . "/";
        //$webUrl = "https://munchado.com/";
        $contact = [];
        $limit = 100;
        $totalRecord = count($emails);
        $pages = ($totalRecord / $limit);
        $totalpage = ceil($pages);

        for ($i = 1; $i <= $totalpage; $i++) {
            $contact = [];
            $offset = 0;
            if ($i > 0) {
                $page = ($i < 1) ? 1 : $i;
                $offset = ($i - 1) * ($limit);
            }



            $final = array_slice($emails, $offset, $limit);
            foreach ($final as $key => $value) {
                if (!empty($value['email'])) {
                    if ($value['restaurant_id']) {
                        $story = $this->restaurantStory($value['restaurant_id']);
                        $urlRestName = str_replace(" ", "-", strtolower(trim($value['restaurant_name'])));
                        $restaurantUrl = $webUrl . "loginRedirect?url=restaurants/" . $urlRestName . "/" . $value['restaurant_id'];
                        //$contact[] = array("contact" => array('email' => $value['email']), 'properties' => array('detail12' => $webUrl, 'restaurant_name' => $value['restaurant_name'], 'restaurant_id' => $value['restaurant_id'], 'restaurant_url' => $restaurantUrl));
                        $contact[] = array("contact" => array('email' => $value['email']), 'properties' => array('detail12' => $webUrl, 'restaurant_name' => $value['restaurant_name'], 'restaurant_id' => $value['restaurant_id'], 'restaurant_url' => $restaurantUrl, 'detail13' => $story));
                    } else {
                        //$contact[] = array("contact" => array('email' => $value['email']), 'properties' => array('detail12' => $webUrl));
                        $contact[] = array("contact" => array('email' => $value['email']), 'properties' => array('detail12' => $webUrl, 'detail13' => ''));
                    }
                }
            }

            $data = array(
                'clientId' => $this->salesmanagoClientId,
                'apiKey' => $this->salesmanagoApiKey,
                'requestTime' => time(),
                'sha' => sha1($this->salesmanagoApiKey . $this->salesmanagoClientId . $this->salesmanagoApiSecret),
                'owner' => self::OWNER_EMAIL,
                'upsertDetails' => $contact,
            );
            $json = json_encode($data);

            $result = $this->postToSalesmanago('http://' . $this->salesmanagoEndpoint . '/api/contact/batchupsert', $json, 'POST');
            MUtility\MunchLogger::writeLog(new \Exception('SalesManago-Response'), 4, json_encode($result));
            MUtility\MunchLogger::writeLogSalesmanago(new \Exception('SalesManago-Response'), 4, $json);
        }
        return array(true);
    }

    public function registrationEvent($data) {
        $email = $data['email'];
        $description = "Dine_and_More";
        $restaurantName = isset($data['restaurant_name']) ? $data['restaurant_name'] : "";
        $location = isset($data['location']) ? $data['location'] : "";
        $value = 200;
        $contactExtEventType = "OTHER";
        //$detail1 = isset($data['detail1'])?$data['detail1']:"";
        $restaurantId = isset($data['restaurant_id']) ? $data['restaurant_id'] : "";
        $webUrl = $data['weburl'];
        $restaurantUrl = $data['restaurant_url'];
        $externalId = isset($data['externalId']) ? $data['externalId'] : "";
        $dt = new \DateTime("NOW");
        $restaurantFunctions = new \Restaurant\RestaurantDetailsFunctions();

        $salesMongoData = array(
            'clientId' => $this->salesmanagoClientId,
            'apiKey' => $this->salesmanagoApiKey,
            'requestTime' => time(),
            'sha' => sha1($this->salesmanagoApiKey . $this->salesmanagoClientId . $this->salesmanagoApiSecret),
            'owner' => self::OWNER_EMAIL,
            //'tags' => isset($data['tags'])?$data['tags']:"",
            'email' => $email,
            'contactEvent' => Array
                (
                'date' => $dt->format('c'),
                'description' => $description,
                'products' => $restaurantName,
                'location' => $restaurantFunctions->restaurantAddress($restaurantId),
                'value' => $value,
                'contactExtEventType' => $contactExtEventType,
                'detail1' => $restaurantId,
                'detail2' => $restaurantUrl,
                'detail8' => $webUrl . "/",
                'externalId' => $externalId,
                'detail15' => $data['story'],
            ),
        );
        //pr($salesMongoData,1);
        $json = json_encode($salesMongoData);
        $result = $this->postToSalesmanago('http://www.salesmanago.pl/api/contact/addContactExtEvent', $json, 'POST');
        MUtility\MunchLogger::writeLog(new \Exception('SalesManago-Response'), 4, json_encode($result));
        MUtility\MunchLogger::writeLogSalesmanago(new \Exception('SalesManago-Response'), 4, "Registration Event: " . $json);
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