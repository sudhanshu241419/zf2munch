<?php
namespace Restaurant\Model;

use MCommons\Model\AbstractModel;
use MCommons\StaticOptions;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;
use User\UserFunctions;
use User\Model\UserOrder;

class PreOrder extends AbstractModel
{

    public $id;

    public $restaurant_id;

    public $user_id;

    public $user_sess_id;

    public $state_code;

    public $city;

    public $address;

    public $order_type;

    public $order_token;

    public $order_status;

    public $delivery_time;

    public $min_order_exceed;

    public $sub_total;

    public $delivery_charges;

    public $tax;

    public $tip;

    public $tip_percent;

    public $order_type1;

    public $zipcode;

    public $order_payee_id;

    public $order_payee_email;

    public $order_submitted_permission;

    public $created_at;

    public $updated_at;

    protected $_db_table_name = 'Restaurant\Model\DbTable\PreOrderTable';

    protected $_primary_key = 'id';

    const ORDER_PENDING = '0';

    const ORDER_CHECKOUT = '1';

    public function getPreOrderDetails($preOrderId)
    {
        $select = new Select();
        $select->from($this->getDbTable()
            ->getTableName());
        $select->columns(array(
            'id',
            'restaurant_id',
            'created_at',
            'order_status',
            'delivery_time',
            'sub_total',
            'delivery_charges',
            'tax',
            'tip',
            'order_type',
            'order_type1',
            'discount',
            'address'
        ));
        $select->join(array(
            'rs' => 'restaurants'
        ), 'rs.id =  pre_order.restaurant_id', array(
            'restaurant_name',
            'restaurant_id' => 'id'
        ), $select::JOIN_INNER);
        $select->join(array(
            'poi' => 'pre_order_item'
        ), 'poi.pre_order_id = pre_order.id', array(
            
            'item_name' => 'item',
            'item_qty' => 'quantity'
        ), $select::JOIN_INNER);
        $where = new Where();
        $where->equalTo('pre_order.id', $preOrderId);
        $select->where($where);
        $orderData = $this->getDbTable()
            ->setArrayObjectPrototype('ArrayObject')
            ->getReadGateway()
            ->selectWith($select)
            ->current();
        return $orderData;
    }

    public function initialize_pre_order_data($order_type, $delivery_charge, $min_order_value)
    {
        return array(
            'min_order_exceed' => $min_order_value,
            'order_info' => array(
                'sub_total' => 0,
                'delivery_charges' => $delivery_charge,
                'tax' => 0,
                'tip' => 0,
                'tip_percent' => 10,
                'tip_text' => '10% ($0.00)',
                'discount' => 0,
                'order_type1' => $order_type,
                'item_info' => array()
            ),
            'delivery_addresses' => array()
        );
    }

    public function getDetails($restaurant_id, $statusType, $token)
    {
        $preOrderResponse = array();
        // Get restaurant details data
        $restauratDetailModel = new RestaurantDetail();
        $restaurantData = $restauratDetailModel->find(array(
            'columns' => array(
                'minimum_delivery',
                'delivery_charge'
            ),
            'where' => array(
                'restaurant_id' => $restaurant_id
            )
        ))->current();
        $preOrderResponse = $this->initialize_pre_order_data('I', 0, 0);
        $preOrderResponse['min_order_exceed'] = $restaurantData->minimum_delivery;
        $preOrderResponse['order_info']['delivery_charges'] = $restaurantData->delivery_charge;
        $preOrderData = $this->find(array(
            'columns' => array(
                'id',
                'user_id',
                'delivery_time',
                'sub_total',
                'address',
                'created_at'
            ),
            'where' => array(
                'user_sess_id' => $token,
                'restaurant_id' => $restaurant_id,
                'order_status' => self::ORDER_PENDING
            )
        ))->toArray();
        // Get Preorder latest data if available
        // print_r($preOrderData);
        return $preOrderResponse;
    }

    public function addtoPreOrder()
    {
        $data = $this->toArray();
        
        $deliveryTime = $this->manipulateDeliveryTime($data['delivery_time'], $data['restaurant_id']);
        $data['delivery_time'] = $deliveryTime;
        
        $writeGateway = $this->getDbTable()->getWriteGateway();
        
        if (! $this->id) {
            $rowsAffected = $writeGateway->insert($data);
        } else {
            $rowsAffected = $writeGateway->update($data, array(
                'id' => $this->id
            ));
        }
        // Get the last insert id and update the model accordingly
        $lastInsertId = $writeGateway->getAdapter()
            ->getDriver()
            ->getLastGeneratedValue();
        
        if ($rowsAffected >= 1) {
            $this->id = $lastInsertId;
            return $this->toArray();
        }
        return false;
    }

    public function manipulateDeliveryTime($deliveryTime, $restaurant_id)
    {
        $currDateTime = StaticOptions::getRelativeCityDateTime(array(
            'restaurant_id' => $restaurant_id
        ));
        
        $currentDateTime = $currDateTime->format('Y-m-d H:i:s');
        $currentDate = $currDateTime->format('Y-m-d');
        if (! empty($deliveryTime)) {
            $dateArr = explode('#', $deliveryTime);
            
            if ($deliveryTime == 'TODAY#ASAP') {
                $timeInterval = $this->roundToNearestInterval(strtotime($currentDateTime));
                $deliveryTime = date('Y-m-d H:i:s', $timeInterval);
                // $deliveryTime=StaticOptions::getFormattedDateTime ( $time, 'H:i:s', 'Y-m-d H:i:s' );
            } else 
                if ($dateArr[0] == 'TOMORROW') {
                    $currDateTime->add(new \DateInterval('P1D'));
                    $nextDay = $currDateTime->format('Y-m-d');
                    $deliveryTime = $nextDay . ' ' . $dateArr[1];
                    // $deliveryTime=StaticOptions::getFormattedDateTime ( $dateInterval, 'Y-m-d H:i:s', 'Y-m-d H:i:s' );
                } else {
                    $delivery_date_month = $dateArr[0];
                    $time = explode(' ', $dateArr[1]);
                    
                    if (strtoupper($delivery_date_month) == 'TODAY') {
                        $deliveryTime = date('Y-m-d H:i:s', strtotime($currentDate . ' ' . $time[0]));
                    } else {
                        
                        if ($dateArr[1] == 'ASAP') {
                            $deliveryTime = date('Y-m-d H:i:s', strtotime(trim($delivery_data_arr[1])));
                        } else {
                            $delivery_date_time = $delivery_date_month . " " . $time[0];
                            $deliveryTime = date('Y-m-d H:i:s', strtotime($delivery_date_time));
                        }
                    }
                }
        } else {
            $deliveryTime = $currentDateTime;
        }
        
        return $deliveryTime;
    }

    public function roundToNearestInterval($timestamp)
    {
        $timestamp += 60 * 30;
        list ($m, $d, $y, $h, $i, $s) = explode(' ', date('m d Y H i s', $timestamp));
        if ($s != 0)
            $s = 0;
            // print $i;
        if ($i <= 30) {
            $i = 30;
        } else 
            if ($i < 60) {
                $i = 0;
                $h ++;
            }
        return mktime($h, $i, $s, $m, $d, $y);
    }

    public function userPreOrder($userId, $date, $pandingOrder)
    {
        $select = new Select();
        $select->from($this->getDbTable()
            ->getTableName());
        $select->columns(array(
            'id',
            'restaurant_id',
            'created_at',
            'order_status',
            'delivery_time',
            'sub_total',
            'delivery_charges',
            'tax',
            'tip',
            'order_type'
        ));
        $select->join(array(
            'rs' => 'restaurants'
        ), 'rs.id =  pre_order.restaurant_id', array(
            'restaurant_name'
        ), $select::JOIN_INNER);
         $select->join(array(
        		'poi' => 'pre_order_item'
        ), 'poi.pre_order_id = pre_order.id', array(
        
        		'item_name' => 'item',
        		'item_qty' => 'quantity'
        ), $select::JOIN_INNER); 
        $where = new Where();
        $where->equalTo('pre_order.user_id', $userId);
       // $where->greaterThan('delivery_time', $date);
        $where->equalTo('pre_order.order_status', $pandingOrder);
        $select->where($where);
        $select->order('pre_order.delivery_time ASC');
       //var_dump($select->getSqlString($this->getPlatform('READ'))); die();
        $orderData = $this->getDbTable()
            ->setArrayObjectPrototype('ArrayObject')
            ->getReadGateway()
            ->selectWith($select)
            ->current();
        return $orderData;
    }

    public function getUserAllPreOrder($options = array())
    {
        $select = new Select();
        $select->from($this->getDbTable()
            ->getTableName());
        $select->columns(array(
            'id',
            'restaurant_id',
            'created_at',
            'order_status',
            'delivery_time',
            'sub_total',
            'delivery_charges',
            'tax',
            'tip',
            'order_type'
        ));
        $select->join(array(
            'rs' => 'restaurants'
        ), 'rs.id =  pre_order.restaurant_id', array(
            'restaurant_name',
            'restaurant_id' => 'id'
        ), $select::JOIN_INNER);
        $select->join(array(
            'poi' => 'pre_order_item'
        ), 'poi.pre_order_id = pre_order.id', array(
            
            'item_name' => 'item',
            'item_qty' => 'quantity'
        ), $select::JOIN_INNER);
        $where = new Where();
        $where->equalTo('pre_order.user_id', $options['userId']);
        //$where->greaterThan('pre_order.delivery_time', $options['currentDate']);
        $where->equalTo('pre_order.order_status', $options['orderStatus']);
        $select->where($where);
        $select->order('pre_order.delivery_time ASC');
        //var_dump($select->getSqlString($this->getPlatform('READ'))); die();
        $orderData = $this->getDbTable()
            ->setArrayObjectPrototype('ArrayObject')
            ->getReadGateway()
            ->selectWith($select)
            ->toArray();
        
        if ($orderData) {
            $currentTime = $options['currentDate'];
            
            $orderId = array_unique(array_map(function ($i)
            {
                return $i['id'];
            }, $orderData));
            
            $i = 0;
            $response = array();
            
            foreach ($orderData as $key => $value) {
                
                $response[] = $value;
            }
            
            $order = $this->refineOrder($response, $orderId, $currentTime);
            
            $userFunction = new UserFunctions();
            $res = $userFunction->ReplaceNullInArray($order);
        }
        return isset($res) ? $res : array();
    }

    public function getUserPreOrderCount($userId, $status, $date)
    {
        $select = new Select();
        $select->from($this->getDbTable()
            ->getTableName());
        $select->columns(array(
            
            'total_order' => new Expression('COUNT(id)')
        ));
        $where = new Where();
        $where->equalTo('user_id', $userId);
        $where->equalTo('order_status', $status);
        $where->greaterThan('delivery_time', $date);
        $select->where($where);
        $totalOrder = $this->getDbTable()
            ->setArrayObjectPrototype('ArrayObject')
            ->getReadGateway()
            ->selectWith($select)
            ->current();
        return $totalOrder;
    }

    private function refineOrder($userOrders, $orderId, $currentTime = NULL)
    {
        $userOrderModel = new UserOrder();
        $response = array();
        $orders = array();
        $items = array();
        $orderData = array();
        $index = 0;
        $orderindex = 0;
        $userFunction = new UserFunctions();
        foreach ($orderId as $k => $v) {
            $description = '';
            foreach ($userOrders as $key => $value) {
                if ($v == $value['id']) {
                    $orders[$value['id']]['id'] = $value['id'];
                    $orders[$value['id']]['restaurant_id'] = $value['restaurant_id'];
                    $orders[$value['id']]['restaurant_name'] = $value['restaurant_name'];
                    $orders[$value['id']]['delivery_time'] = StaticOptions::getFormattedDateTime($value['delivery_time'], 'Y-m-d H:i:s', 'M d, Y');
                    $createdAt = StaticOptions::getFormattedDateTime($value['created_at'], 'Y-m-d H:i:s', 'Y-m-d');
                    $orderCreateAt = StaticOptions::getFormattedDateTime($value['created_at'], 'Y-m-d H:i:s', 'Y-m-d H:i:s');
                    if (! empty($currentTime)) {
                        $conpDate = StaticOptions::getFormattedDateTime($currentTime, 'Y-m-d H:i:s', 'Y-m-d');
                        
                        if ($createdAt == $conpDate) {
                            $orders[$value['id']]['order_date'] = $userOrderModel->dateToString($orderCreateAt, $currentTime);
                        } else {
                            $orders[$value['id']]['order_date'] = StaticOptions::getFormattedDateTime($value['created_at'], 'Y-m-d H:i:s', 'M d, Y');
                        }
                    }
                    $orders[$value['id']]['created_at'] = StaticOptions::getFormattedDateTime($value['created_at'], 'Y-m-d H:i:s', 'M d, Y');
                    $orders[$value['id']]['requested_delivery_time'] = StaticOptions::getFormattedDateTime($value['delivery_time'], 'Y-m-d H:i:s', 'h:i A');
                   // $orders[$value['id']]['order_status'] = isset($value['order_status']) ? 'Pending_Order' : 0;
                    $orders[$value['id']]['total_amount'] = number_format($value['sub_total'] + $value['delivery_charges'] + $value['tax'] + $value['tip'], 2);
                    $orders[$value['id']]['order_state'] = 'pending_order';
                    $orders[$value['id']]['order_type'] = $value['order_type'];
                    $description .= $value['item_qty'] . ' ' . $value['item_name'] . ',';
                    $orderDescription = substr($description, 0, - 2);
                    $orders[$value['id']]['description'] = $orderDescription;
                    $orders[$value['id']]['short_description'] = $userFunction->getShortDescription($orderDescription);
                }
            }
        }
        
        $key_index = 0;
        foreach ($orders as $key => $orderItem) {
            $orderResponse[$key_index] = $orderItem;
            $key_index ++;
        }
        
        return $orderResponse;
    }

    public function getUserPreOrder(array $options = array())
    {
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        return $this->find($options)->current();
    }

    public function cancelPreOrder($id, $userId)
    {
        $data = array(
            'order_status' => '2'
        );
        
        $writeGateway = $this->getDbTable()->getWriteGateway();
        $dataUpdated = $writeGateway->update($data, array(
            'id' => $id
        ));
        if ($dataUpdated == 0) {
            throw new \Exception("Invalid Order ID provided", 500);
        } else {
            return array(
                'success' => 'true'
            );
        }
    }
}
