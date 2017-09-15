<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use Restaurant\Model\RestaurantBookmark;
use User\UserFunctions;
use Zend\Db\Sql\Predicate\Expression;

class WebRestaurantBookmarksCountController extends AbstractRestfulController {

    public function getList() {
        $restaurantBookmarksModel = new RestaurantBookmark ();
        $restaurantBookmarksModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $session = $this->getUserSession();
        $userId = $session->getUserId();
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
            'joins' => $joins,
            'where' => array(
                'restaurant_bookmarks.user_id' => $userId
            )
        );
        $bookmarks = $restaurantBookmarksModel->find($options)->toArray();
        $userFunctions = new UserFunctions ();
        $response = $userFunctions->arrangeRestaurantBookmarks($bookmarks);
        $total = count($response);
        $response = $this->getCounts($response);
        $response['total'] = $total;
        return $response;
    }

    private function getCounts($response) {
        $lovedIt = 0;
        $beenThere = 0;
        $craveIt = 0;

        foreach ($response as $single) {
            if ($single ['loved_it']) {
                $lovedIt ++;
            }
            if ($single ['been_there']) {
                $beenThere ++;
            }
            if ($single ['crave_it']) {
                $craveIt ++;
            }
        }
        return array(
            'loved_it_count' => $lovedIt,
            'been_there_count' => $beenThere,
            'crave_it_count' => $craveIt
        );
    }

}
