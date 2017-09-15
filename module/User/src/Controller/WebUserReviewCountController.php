<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\Model\UserReview;
use User\Model\UserReservation;
use Zend\Db\Sql\Predicate\Expression;
use Restaurant\Model\Restaurant;

class WebUserReviewCountController extends AbstractRestfulController {

    const FORCE_LOGIN = true;

    public function getList() {
        $reservationModel = new UserReservation();
        $restaurantModel = new Restaurant();
        $unReviewCount = 0;
        $reviewCount = array();
        $queryParams = $this->getRequest()->getQuery()->toArray();
        $userId = $this->getUserSession()->user_id;
        $options = array(
            'columns' => array(
                'count' => new \Zend\Db\Sql\Expression('COUNT(*)')
            ),
            'where' => array(
                'user_id' => $userId,
                'status' => array(0, 1, 2)
            )
        );
        $userReviewModel = new UserReview ();
        $userReviewModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $response = $userReviewModel->find($options)->toArray();
        /* Un-Reviewd count */
        $archiveReservations = $reservationModel->getArchiveList($userId);
        $joins [] = array(
            'name' => array(
                'uo' => 'user_orders'
            ),
            'on' => new Expression("(restaurants.id = uo.restaurant_id AND uo.user_id = " . $userId . " AND uo.is_reviewed = 0 AND uo.status='archived')"),
            'columns' => array(
                'order_id' => 'id',
                'type' => 'order_type',
                'created_at'
            ),
            'type' => 'inner'
        );

        $options = array(
            'columns' => array(
                'restaurant_id' => 'id',
                'restaurant_name'
            ),
            'joins' => $joins,
            'order' => array(
                'uo.created_at' => 'desc'
            )
        );
        $restaurantModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $archiveOrders = $restaurantModel->find($options)->toArray();
        $unReviewCount = count($archiveReservations) + count($archiveOrders);
        $reviewCount = current($response);
        return (array('count' => $reviewCount['count'], 'unReviewCount' => $unReviewCount));
    }

}
