<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\Model\UserReview;
use MCommons\StaticOptions;
use User\UserFunctions;

class WebUserReviewController extends AbstractRestfulController {

    const FORCE_LOGIN = true;

    private static $orderMapping = array(
        'created_on' => 'desc',
        'rating' => 'desc',
        'restaurant_name' => 'asc',
        'review_for' => 'asc'
    );

    public function getList() {
        $queryParams = $this->getRequest()->getQuery()->toArray();
        $userId = $this->getUserSession()->user_id;
        $joins = array();
        $joins [] = array(
            'name' => 'restaurants',
            'on' => 'restaurants.id = user_reviews.restaurant_id',
            'columns' => array(
                'restaurant_name'
            ),
            'type' => 'left'
        );
        $order = $this->getQueryParams('sort');
        if (!preg_match('/(restaurant_name|rating|created_on|review_for)$/', $order)) {
            $order = false;
        }
        $limit = $this->getQueryParams('limit');
        $offset = $this->getQueryParams('offset');
        $limit = $limit ? $limit : 50;
        $offset = $offset ? $offset : 0;
        if (isset($queryParams ['page']) && $queryParams ['page'] != null) {
            $userFunctions = new UserFunctions();
            $limit = '50';
            $offset = $userFunctions->getOffsetFromPage($queryParams ['page']);
        }
        $options = array(
            'columns' => array(
                'id',
                'created_on',
                'rating',
                'review_for',
                'status'
            ),
            'joins' => $joins,
            'where' => array(
                'user_id' => $userId,
                'status' => array(0, 1, 2)
            ),
            'limit' => $limit,
            'offset' => $offset,
            'order' => $order ? array(
                $order => self::$orderMapping[$order]
                    ) : 'created_on desc'
        );
        $userReviewModel = new UserReview ();
        $userReviewModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $response = $userReviewModel->find($options)->toArray();
        foreach ($response as $key => $value) {
            $response [$key] ['created_on'] = StaticOptions::getFormattedDateTime($response [$key] ['created_on'], 'Y-m-d H:i:s', 'd M, Y');
            if ($value['status'] == 2)
                $response [$key] ['status_str'] = "Rejected";
            if ($value['status'] == 3)
                $response [$key] ['status_str'] = "Deleted";
        }
        return $response;
    }

}
