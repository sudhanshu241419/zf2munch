<?php
use MCommons\StaticOptions;
use User\Model\UserReservation;
use Zend\Db\Sql\Predicate\Expression;

defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'local'));
require_once dirname(__FILE__) . "/../../../jobs/init.php";
StaticOptions::setServiceLocator($GLOBALS ['application']->getServiceManager());
$sl = StaticOptions::getServiceLocator();
$config = $sl->get('Config');
$reservationModel = new UserReservation();
$joins [] = array(
    'name' => array(
        'r' => 'restaurants'
    ),
    'on' => new Expression("(user_reservations.restaurant_id = r.id)"),
    'columns' => array(
        'city_id'
    ),
    'type' => 'left'
);
$joins [] = array(
    'name' => array(
        'ci' => 'cities'
    ),
    'on' => new Expression("(ci.id = r.city_id)"),
    'columns' => array(
        'time_zone'
    ),
    'type' => 'left'
);
$options = array(
    'columns' => array(
        'restaurant_name',
        'reserved_seats',
        'phone',
        'time_slot',
        'restaurant_id', 'id', 'host_name'
    ),
    'joins' => $joins,
    'where' => array('user_reservations.status = "4" and user_reservations.cron_status ="0" and user_reservations.cronUpdate="0" and user_reservations.host_name ="aria"')
);
$reservationModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
$allReservation = $reservationModel->find($options)->toArray();
$cronUpdate = 0;
if (!empty($allReservation)) {
    foreach ($allReservation as $key => $value) {
        $currentTime = new \DateTime ();
        $currentTime->setTimezone(new \DateTimeZone($value ['time_zone']));
        $arrivedTime = \DateTime::createFromFormat(StaticOptions::MYSQL_DATE_FORMAT, $value ['time_slot'], new \DateTimeZone($value ['time_zone']));
        $currentTimeNew = StaticOptions::getRelativeCityDateTime(array(
                    'restaurant_id' => $value ['restaurant_id']
                ))->format(StaticOptions::MYSQL_DATE_FORMAT);
        $currentDate = StaticOptions::getDateTime()->format(StaticOptions::MYSQL_DATE_FORMAT);
        $differenceOfTimeInMin = round(abs(strtotime($arrivedTime->format("Y-m-d H:i:s")) - strtotime($currentTimeNew)) / 60);
        if (strtotime($arrivedTime->format("Y-m-d H:i:s")) > strtotime($currentTimeNew)) {
            if (($differenceOfTimeInMin > 25) && ($differenceOfTimeInMin < 31)) {
                $userSmsData = array();
                $specChar = $config ['constants']['special_character'];
                $userSmsData['user_mob_no'] = $value ['phone'];
                $userSmsData['message'] = "Hey! Don't forget, your reservation at " . strtr($value['restaurant_name'], $specChar) . " is in 30 minutes. You will be charged $20 per person if your party doesn't show.";
                StaticOptions::sendSmsClickaTell($userSmsData, 0);
                $cronUpdate = 1;
            }
        }
        if ($cronUpdate == 1) {
            $reservationModel->updateCronReservation($value['id']);
        }
    }
}