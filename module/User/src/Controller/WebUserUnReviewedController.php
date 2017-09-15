<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use Restaurant\Model\Restaurant;
use Zend\Db\Sql\Predicate\Expression;
use MCommons\StaticOptions;
use User\Model\UserReservation;
use User\UserFunctions;

class WebUserUnReviewedController extends AbstractRestfulController {

    const FORCE_LOGIN = true;

    public function getList() {
        $allReview = array();
        $userId = $this->getUserSession()->user_id;
        $restaurantModel = new Restaurant ();
        $reservationModel = new UserReservation();
        $userFunction = new UserFunctions();
        $session = $this->getUserSession();
        $locationData = $session->getUserDetail('selected_location');
        $currentDate = $userFunction->userCityTimeZone($locationData);
        $joins = array();
        $archiveReservations = $reservationModel->getArchiveList($userId, $currentDate);
        $joins [] = array(
            'name' => array(
                'uo' => 'user_orders'
            ),
            'on' => new Expression("(restaurants.id = uo.restaurant_id AND uo.user_id = " . $userId . " AND uo.is_reviewed = 0 AND (uo.status='archived' OR uo.status='delivered' OR uo.status='arrived') )"),
            'columns' => array(
                'order_id' => 'id',
                'type' => 'order_type',
                'created_at'
            ),
            'type' => 'inner'
        );
        $limit = SHOW_PER_PAGE;
        $page = $this->getQueryParams('page');
        $limit = $limit ? $limit : 50;
        $offset = 0;

        if ($page > 0) {
            $page = ($page < 1) ? 1 : $page;
            $offset = ($page - 1) * ($limit);
        }
        $options = array(
            'columns' => array(
                'restaurant_id' => 'id',
                'restaurant_name',
                'closed',
                'inactive'
            ),
            'joins' => $joins,
            'order' => array(
                'uo.created_at' => 'desc'
            )
        );
        $restaurantModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $archiveOrders = $restaurantModel->find($options)->toArray();
        if (!empty($archiveOrders) && !empty($archiveReservations)) {

            $allReview = array_merge($archiveOrders, $archiveReservations);
        } elseif (!empty($archiveReservations)) {
            $allReview = $archiveReservations;
        } elseif (!empty($archiveOrders)) {
            $allReview = $archiveOrders;
        }

        foreach ($allReview as $key => $value) {
            if (!isset($allReview [$key] ['type'])) {
                $allReview [$key] ['type'] = '';
            }
            switch ($allReview [$key] ['type']) {
                case 'Delivery' :
                    $allReview [$key] ['type'] = '1';

                    break;
                case 'Takeout' :
                    $allReview [$key] ['type'] = '2';

                    break;
                default :
                    $allReview [$key] ['type'] = '3';
                    break;
            }
        }
        uasort($allReview, array(
            $this,
            'date_compare'
        ));
        $reservations = array_values($allReview);
        foreach ($reservations as $key => $value) {
            $reservations[$key]['id'] = $key + 1;
            if ($reservations [$key] ['created_at'] != null) {
                $reservations [$key] ['created_at'] = StaticOptions::getFormattedDateTime($reservations [$key] ['created_at'], 'Y-m-d H:i:s', 'd M, Y');
            }
        }
        if ($reservations) {
            $reservations = array_slice($reservations, $offset, $limit);
        }
        return $reservations;
    }

    function date_compare($a, $b) {
        $t1 = strtotime($a ['created_at']);
        $t2 = strtotime($b ['created_at']);
        $t3 = ($t1 > $t2) ? - 1 : 1;
        return $t3;
    }

}
