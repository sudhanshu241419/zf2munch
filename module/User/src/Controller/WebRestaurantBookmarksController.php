<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use Restaurant\Model\RestaurantBookmark;
use User\Model\User;
use User\UserFunctions;
use MCommons\StaticOptions;
use Zend\Db\Sql\Predicate\Expression;

class WebRestaurantBookmarksController extends AbstractRestfulController {

    public function getList() {
        $queryParams = $this->getRequest()->getQuery()->toArray();
        $session = $this->getUserSession();
        if ($session) {
            $login = $session->isLoggedIn();
            if (!$login) {
                throw new \Exception('No Active Login Found.');
            }
        } else {
            throw new \Exception('No Active Login Found.');
        }
        $userId = $session->getUserId();
        $restaurantBookmarks = new RestaurantBookmark ();
        $restaurantBookmarks->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $order = array(
            'created_on' => 'desc'
        );
        if (isset($queryParams ['sort'])) {
            if ($queryParams ['sort'] == 'date') {
                $order = array(
                    'restaurant_bookmarks.created_on' => 'desc'
                );
            } elseif ($queryParams ['sort'] == 'alphabetical') {
                $order = array(
                    'restaurant_bookmarks.restaurant_name' => 'asc'
                );
            }
        }
        $joins[] = array(
            'name' => array(
                'r' => 'restaurants'
            ),
            'on' => new Expression("(r.id = restaurant_bookmarks.restaurant_id)"),
            'columns' => array(
                'closed', 'inactive'
            ),
            'type' => 'left'
        );
        $options = array(
            'columns' => array(
                'restaurant_id',
                'restaurant_name',
                'type',
                'created_on'
            ),
            'joins' => $joins,
            'where' => array(
                'restaurant_bookmarks.user_id' => $userId
            ),
            'order' => $order
        );

        $restaurantBookmarkDetails = $restaurantBookmarks->find($options)->toArray();
        $userFunctions = new UserFunctions ();
        $response = $userFunctions->arrangeRestaurantBookmarks($restaurantBookmarkDetails);
        // query need to be optimized
        if (isset($queryParams ['sort'])) {
            if ($queryParams ['sort'] == 'date') {
                uasort($response, array(
                    $this,
                    'date_compare'
                ));
            }
        }
        $length = isset($queryParams ['limit']) ? $queryParams ['limit'] : '50';
        $offset = isset($queryParams ['offset']) ? $queryParams ['offset'] : '0';
        if (isset($queryParams ['page']) && $queryParams ['page'] != null) {
            $length = '50';
            $offset = $userFunctions->getOffsetFromPage($queryParams ['page']);
        }
        $finalResponse = array_slice($response, $offset, $length);
        foreach ($finalResponse as $key => $value) {
            if ($finalResponse [$key] ['created_on'] != null) {
                $finalResponse [$key] ['created_on'] = StaticOptions::getFormattedDateTime($finalResponse [$key] ['created_on'], 'Y-m-d H:i:s', 'd M, Y');
            } else {
                $finalResponse [$key] ['created_on'] = '';
            }
            $userFunctions->userId = $userId;
            $userFunctions->restaurantId = $value['restaurant_id'];
            $finalResponse [$key]['dine_and_more'] = (!$userFunctions->isRegisterWithRestaurant($userId))?1:0;
        }
        return $finalResponse;
    }

    function date_compare($a, $b) {
        $t1 = strtotime($a ['created_on']);
        $t2 = strtotime($b ['created_on']);
        $t3 = ($t1 > $t2) ? - 1 : 1;
        return $t3;
    }

}
