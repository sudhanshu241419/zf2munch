<?php

use MCommons\StaticOptions;

class Postmates {

    public static $customerId;
    public static $user;
    public static $apiUrl;
    private $password = '';
    private $result;

    const DELIVERY_QUOTE = "delivery_quotes";
    const CREATE_DELIVERY = "deliveries";

    public function __construct() {
        $config = StaticOptions::getServiceLocator()->get('config');
        self::$customerId = $config['constants']['postmates']['customer_id'];
        self::$user = $config['constants']['postmates']['api_key'];
        self::$apiUrl = $config['constants']['postmates']['api_request'];
    }

    /*
     * Evaluate whether the quoted price and delivery estimate meets your needs
     * Create a Delivery 
     */

    public function getDeliveryPrice(array $data = array()) {
        if (empty($data)) {
            return $this->result = false;
        }
        $apikey = self::$user;
        $this->createRequestUrl(self::CREATE_DELIVERY);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::$apiUrl);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "$apikey:$this->password");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//optional
        $this->result = curl_exec($ch);
        curl_close($ch);
        return $this->result;
    }

    /*
     * Request a Delivery quote 
     */

    public function getDeliveryQuote(array $data = array()) {
        if (empty($data)) {
            return $this->result = false;
        }
        $apikey = self::$user;
        $this->createRequestUrl(self::DELIVERY_QUOTE);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::$apiUrl);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "$apikey:$this->password");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//optional
        $this->result = curl_exec($ch);
        curl_close($ch);
        return $this->result;
    }

    /*
     * To check the status of Delivery
     */

    function getDiliveryStatus($deliveryId) {
        if (!$id) {
            return $this->result = false;
        }
        $apikey = self::$user;
        $this->createRequestUrl(self::CREATE_DELIVERY, $deliveryId);
        $ch = curl_init();
        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//optional
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, self::$apiUrl);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "$apikey:$this->password");
        $this->result = curl_exec($ch);
        curl_close($ch);
        return $this->result;
    }

    private function createRequestUrl($serviceType, $deliveryId = FALSE) {
        if ($deliveryId) {
            self::$apiUrl = self::$apiUrl . self::$customerId . "/" . $serviceType . "/" . $deliveryId;
        } else {
            self::$apiUrl = self::$apiUrl . self::$customerId . "/" . $serviceType;
        }
    }

}
