<?php

use MCommons\StaticOptions;

class DeliveryProvider {

    private $template;
    private $subject;
    private $sender = '';
    private $senderName = '';
    private $recievers;
    private $recieversCc;

    const STATUS = 'ordered';

    private $restaurantId;
    private $restaurant;
    private $orderStatus;

    /**
     * 
     * @param type $status string
     * @param type $restaurantId int
     * @param type $restaurant object
     */
    public function __construct($status, $restaurantId, $restaurant) {
        $config = StaticOptions::getServiceLocator()->get('config');
        $this->orderStatus = $status;
        if ($this->orderStatus == self::STATUS) {
            $this->template = 'place-order-delivery-service-provider';
            $this->subject = 'New Delivery Order From a Munch Ado Customer';
        } else {
            $this->template = 'preorder-user-mail-service-provider';
            $this->subject = 'Planning Ahead We See';
        }
        $this->recievers = $config['constants']['delivery_service']['service_provider_email'];
        $this->recieversCc = unserialize($config['constants']['delivery_service']['cc_email']);
        $this->sender = $config['constants']['delivery_service']['sender'];
        $this->senderName = $config['constants']['delivery_service']['sender_name'];
        $this->restaurantId = $restaurantId;
        $this->restaurant = $restaurant;
    }

    /**
     * @data array
     * Evaluate whether the quoted price and delivery estimate meets your needs
     * Create a Delivery 
     */
    public function sendMailsToServiceProvider($data = array()) {

        $template = "email-template/" . $this->template;
        $options = array("where" => array('id' => $this->restaurantId));
        $restaurantDetail = $this->restaurant->findRestaurant($options);
        $txtFileContent = $this->createMailContent($restaurantDetail, $data);
        $orderDetailTextFileForServiceProvider = $this->createTextFileOfOrderForServiceprovider($txtFileContent, $data['receiptNo']);
        $emailDataForServiceProvider = array(
            'receiver' => $this->recievers,
            'receiverCc' => $this->recieversCc,
            'variables' => $data,
            'subject' => $this->subject,
            'template' => $template,
            'attachment' => $orderDetailTextFileForServiceProvider
        );
        StaticOptions::sendMailToServiceProvider($emailDataForServiceProvider);
        unlink(SAMPLE_TEXT_FILE . "/" . $data['receiptNo'] . "_orderDetail.txt");
    }

    private function createTextFileOfOrderForServiceprovider(array $data = array(), $recieptNo) {
        $filePath = array();
        if ($data) {
            $filePath = SAMPLE_TEXT_FILE . "/" . $recieptNo . "_orderDetail.txt";
            $newFile = fopen($filePath, 'w+');
            fwrite($newFile, $data);
            fclose($newFile);
            chmod($filePath, 0777);
            $filePath = array('filepath' => $filePath, 'filename' => $recieptNo . "_orderDetail.txt");
        }
        return $filePath;
    }

    public function createMailContent($restaurantDetail, $data) {
        $see = "";
        $room = "";
        $orderNotes = "";
        $orderNotes = '"cc:' . $data['cardNo'] . ', SubTotal:' . $data['subtotal'] . ', Tax:' . $data['tax'] . ', Tip:' . $data['tipAmount'] . ', Service:0, Delivery:' . $data['deliveryCharge'] . ', Total:' . $data['total'] . '"';
        $deliveryDate = date("m/d/Y", strtotime($data['onlyDate']));
        $deliveryTime = date("H:i", strtotime($data['onlyTime']));
        $orderTime = date("H:i", strtotime($data['orderTime']));
        $orderDate = date("m/d/Y", strtotime($data['orderDate']));
        $deliveryPickUpTime = $this->getDeliveryPickupTime($deliveryDate, $deliveryTime, $orderDate, $orderTime, $this->orderStatus);
        if ($deliveryPickUpTime) {
            $deliverypickUpDateTime = explode(" ", $deliveryPickUpTime);
        } else {
            $deliverypickUpDateTime[0] = 0;
            $deliverypickUpDateTime[1] = 0;
        }
        $contentHeading = "acct#\tPu shipto\td name\td address\td city\td state\td zip\td phone\td see\td room\td note\tready date\tready time\tdue date\tdue time\tauth\torder notes\n";
        $content = MUNCHADO_ACCOUNT_AT_SERVICE_PROVIDER . "\t" . $restaurantDetail->rest_code . "\t" . $data['name'] . "\t" . $data['address'] . "\t" . $data['city'] . "\t" . $data['state'] . "\t" . $data['zip'] . "\t" . $data['phone'] . "\t" . $see . "\t" . $room . "\t" . $data['specialInstructions'] . "\t" . $deliverypickUpDateTime[0] . "\t" . $deliverypickUpDateTime[1] . "\t" . $deliveryDate . "\t" . $deliveryTime . "\t" . $data['receiptNo'] . "\t" . $orderNotes . "\n";
        return $txtFileContent = $contentHeading . $content;
    }

    public function getDeliveryPickupTime($deliveryDate, $deliveryTime, $orderDate, $orderTime, $status) {
        $pickuptime = false;
        if ($deliveryTime && $orderTime) {

            if ($status == "ordered") {
                $orderDateTime = $orderDate . " " . $orderTime;
                $deliveryDateTime = $deliveryDate . " " . $deliveryTime;
                $deliveryDateTimeObj = new \DateTime($deliveryDateTime);
                $orderedDateTimeObj = new \DateTime($orderDateTime);
                $dateDefference = $deliveryDateTimeObj->diff($orderedDateTimeObj);
                $differenceHourMin = $dateDefference->format("%H:%I");
                $differenceHourMinArr = explode(":", $differenceHourMin);

                if ($differenceHourMinArr[0] != "00") {
                    $min = ($differenceHourMinArr[0] * 60) + $differenceHourMinArr[1];
                } else {
                    $min = $differenceHourMinArr[1];
                }

                if ($min > 45) {
                    $deliveryTimeStamp = strtotime($deliveryDateTime);
                    $pickuptime = date("m/d/Y H:i", strtotime(PRE_ORDER_PICKUP_TIME, $deliveryTimeStamp));
                } elseif ($min <= 45) {
                    $orderTimeStamp = strtotime($orderDateTime);
                    $pickuptime = date("m/d/Y H:i", strtotime(CURRENT_ORDER_PICKUP_TIME, $orderTimeStamp));
                }
            } else {
                $deliveryDateTime = $deliveryDate . " " . $deliveryTime;
                $deliveryTimeStamp = strtotime($deliveryDateTime);
                $pickuptime = date("m/d/Y H:i", strtotime(PRE_ORDER_PICKUP_TIME, $deliveryTimeStamp));
            }
        }
        return $pickuptime;
    }

}
