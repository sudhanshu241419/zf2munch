<?php
namespace User\Model;

use MCommons\Model\AbstractModel;
use MCommons\StaticOptions;
use Restaurant\Model\Restaurant;
use User\Controller\WebUserOrderController;
use User\UserFunctions;

class UserCron extends AbstractModel
{

    public $id;

    public $order_id;

    public $delivery_time;

    public $arrived_time;

    public $archive_time;

    public $time_zone;

    public $status;

    protected $_db_table_name = 'User\Model\DbTable\UserCronTable';

    public function setOrderStatusArchived($response)
    {
        $cronOrderModel = new UserCron();
        // set status to 2 for cron order
        
        $predicate = array(
            'id' => $response['id']
        );
        $data = array(
            'status' => 2
        );
        $cronOrderModel->abstractUpdate($data, $predicate);
        
        // set status to archived in user orders
        $predicate = array(
            'id' => $response['order_id']
        );
        $data = array(
            'status' => 'archived'
        );
        $userOrder = new UserOrder();
        $userOrder->abstractUpdate($data, $predicate);
    }

    public function setOrderStatusArrived($response)
    {
        $cronOrderModel = new UserCron();
        // set status to 1 for cron order
        $predicate = array(
            'id' => $response['id']
        );
        $data = array(
            'status' => 1
        );
        $cronOrderModel->abstractUpdate($data, $predicate);
        self::sendDeliveryMail($response['order_id']);
        // set status to arrived in user orders
        $predicate = array(
            'id' => $response['order_id']
        );
        $data = array(
            'status' => 'arrived'
        );
        $userOrder = new UserOrder();
        $userOrder->abstractUpdate($data, $predicate);
    }

    private static function sendDeliveryMail($orderId)
    {
        $userOrderModel = new UserOrder();
        $userModel = new User();
        $order = $userOrderModel->getUserOrder(array(
            'columns' => array(
                'id',
                'created_at',
                'status',
                'fname',
                'order_type1',
                'order_type',
                'delivery_time',
                'delivery_charge',
                'tax',
                'restaurant_id',
                'tip_amount',
                'order_type',
                'delivery_address',
                'deal_discount',
                'order_amount',
                'card_type',
                'card_number',
                'expired_on',
                'special_checks',
                'user_comments',
                'payment_receipt',
                'total_amount',
                'email',
                'user_id'
            ),
            'where' => array(
                'id' => $orderId
            )
        ));
        $sendMail = $userModel->checkUserForMail($order['user_id'], 'orderconfirm');
        if ($sendMail == true) {
            if (ucfirst($order['order_type']) == 'Delivery') {
                $restaurantId = $order['restaurant_id'];
                $restaurantModel = new Restaurant();
                $userOrderDetailsModel = new UserOrderDetail();
                $orderController = new WebUserOrderController();
                $userFunctionModel = new UserFunctions();
                $orderItemDetails = $userOrderDetailsModel->getOrderDetailItems($orderId);
                $orderData = $orderController->orderDataItemForMail($orderItemDetails);
                $restaurant = $restaurantModel->findRestaurant(array(
                    'columns' => array(
                        'restaurant_name'
                    ),
                    'where' => array(
                        'id' => $restaurantId
                    )
                ));
                $TimeOfDelivery = StaticOptions::getFormattedDateTime($order['delivery_time'], 'Y-m-d H:i:s', 'D, M j, Y');
                $dateOfDelivery = StaticOptions::getFormattedDateTime($order['delivery_time'], 'Y-m-d H:i:s', 'h:i A');
                $dateDevlivery = $TimeOfDelivery . ' at ' . $dateOfDelivery;
                
                $TimeOfOrder = StaticOptions::getFormattedDateTime($order['created_at'], 'Y-m-d H:i:s', 'D, M j, Y');
                $dateOfOrder = StaticOptions::getFormattedDateTime($order['created_at'], 'Y-m-d H:i:s', 'h:i A');
                $dateOrder = '<strong>'.$TimeOfOrder.'</strong>' . ' at ' . '<strong>'.$dateOfOrder.'</strong>';
                
                $recievers = $order['email'];
                $subject = "Food's There!";
                $template = 'food-there';
                $data = array(
                    'name' => $order['fname'],
                    'hostName' => '',
                    'restaurantName' => $restaurant->restaurant_name,
                    'orderType' => ($order['order_type1'] == 'I') ? 'Individual ' . ucwords($order['order_type']) : 'Group ' . ucwords($order['order_type']),
                    'receiptNo' => $order['payment_receipt'],
                    'timeOfOrder' => $dateOrder,
                    'timeOfDelivery' => $dateDevlivery,
                    'orderData' => $orderData,
                    'subtotal' => $order['order_amount'],
                    'discount' => $order['deal_discount'],
                    'tax' => $order['tax'],
                    'tipAmount' => $order['tip_amount'],
                    'total' => $order['total_amount'],
                    'cardType' => isset($order['card_type']) ? $order['card_type'] : '',
                    'cardNo' => $order['card_number'],
                    'expiredOn' => $order['expired_on'],
                    'specialInstructions' => $order['special_checks'],
                    'type' => ucwords($order['order_type']),
                    'address' => $order['delivery_address'],
                    'deliveryCharge' => $order['delivery_charge']
                );
                $emailData = array(
                    'receiver' => $recievers,
                    'variables' => $data,
                    'subject' => $subject,
                    'template' => $template
                );
                // print_r($emailData); die();
                //$userFunctionModel->sendMails($emailData);
            }
        }
    }
}