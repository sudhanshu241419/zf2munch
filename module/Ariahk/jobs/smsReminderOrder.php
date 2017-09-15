<?php

use MCommons\StaticOptions;
use User\Model\UserOrder;
use Zend\Db\Sql\Predicate\Expression;
use Restaurant\OrderFunctions;

defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'local'));
require_once dirname(__FILE__) . "/../../../jobs/init.php";
StaticOptions::setServiceLocator($GLOBALS ['application']->getServiceManager());
$sl = StaticOptions::getServiceLocator();
$config = $sl->get('Config');
$orderModel = new UserOrder();
$orderFunctions = new OrderFunctions();
define('PROTOCOL', 'http://');
$joins [] = array(
    'name' => array(
        'uo' => 'restaurants'
    ),
    'on' => new Expression("(user_orders.restaurant_id = uo.id)"),
    'columns' => array(
        'restaurant_name',
        'city_id',
    ),
    'type' => 'inner'
);
$joins [] = array(
    'name' => array(
        'ci' => 'cities'
    ),
    'on' => new Expression("(ci.id = uo.city_id)"),
    'columns' => array(
        'time_zone'
    ),
    'type' => 'inner'
);
$options = array(
    'columns' => array(
        '*'
    ),
    'joins' => $joins,
    'where' => array('user_orders.status = "confirmed" and user_orders.order_type = "Delivery" and user_orders.cronsmsupdate="0" and user_orders.host_name ="aria"')
);
$orderModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
$allOrders = $orderModel->find($options)->toArray();
$cronUpdate = 0;
if (!empty($allOrders)) {
    foreach ($allOrders as $key => $value) {
        $arrivedTime = \DateTime::createFromFormat(StaticOptions::MYSQL_DATE_FORMAT, $value ['delivery_time'], new \DateTimeZone($value ['time_zone']));
        $created_at = \DateTime::createFromFormat(StaticOptions::MYSQL_DATE_FORMAT, $value ['created_at'], new \DateTimeZone($value ['time_zone']));
        $currentTimeNew = StaticOptions::getRelativeCityDateTime(array(
                    'restaurant_id' => $value ['restaurant_id']
                ))->format(StaticOptions::MYSQL_DATE_FORMAT);
        $differenceOfTimeInMin = round(abs(strtotime($arrivedTime->format("Y-m-d H:i:s")) - strtotime($currentTimeNew)) / 60);
        if (strtotime($currentTimeNew) < strtotime($arrivedTime->format("Y-m-d H:i:s"))) {
            if (($differenceOfTimeInMin > 55) && ($differenceOfTimeInMin < 61)) {
                $userSmsData = array();
                $specChar = $config ['constants']['special_character'];
                $userSmsData['user_mob_no'] = $value ['phone'];
                $userSmsData['message'] = "Your pre-order from Aria will begin preparations in one hour.";
                StaticOptions::sendSmsClickaTell($userSmsData, 0);
                $cronUpdate = 1;
            }
        }
        if ($cronUpdate == 1) {
            $orderModel->updateCronPreOrder($value['id']);
        }
    }
}