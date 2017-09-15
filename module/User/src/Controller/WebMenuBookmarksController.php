<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use Restaurant\Model\MenuBookmark;
use User\Model\User;
use User\UserFunctions;
use MCommons\StaticOptions;
use Zend\Db\Sql\Predicate\Expression;

class WebMenuBookmarksController extends AbstractRestfulController {

    public function getList() {
        $queryParams = $this->getRequest()->getQuery()->toArray();
        $session = $this->getUserSession();
        if ($session) {
            $userid = $session->getUserId();
        } else {
            throw new \Exception('No Active Login Found.');
        }
        $menuBookmarks = new MenuBookmark ();
        $menuBookmarks->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $order = array(
            'created_on' => 'desc'
        );
        if (isset($queryParams ['sort'])) {
            if ($queryParams ['sort'] == 'date') {
                $order = array(
                    'created_on' => 'desc'
                );
            } elseif ($queryParams ['sort'] == 'alphabetical') {
                $order = array(
                    'menu_name' => 'asc'
                );
            }
        }
        $joins[] = array(
            'name' => array(
                'r' => 'restaurants'
            ),
            'on' => new Expression("(r.id = menu_bookmarks.restaurant_id)"),
            'columns' => array(
                'restaurant_name',
                'closed',
                'inactive',
            ),
            'type' => 'left'
        );
        $joins[] = array(
            'name' => array(
                'm' => 'menus'
            ),
            'on' => new Expression("(m.id = menu_bookmarks.menu_id)"),
            'columns' => array(
                'status'
            ),
            'type' => 'Inner'
        );
        $options = array(
            'columns' => array(
                'restaurant_id',
                'menu_id',
                'menu_name',
                'type',
                'created_on'
            ),
            'where' => array(
                'user_id' => $userid
            ),
            'joins' => $joins,
            'order' => $order
        );

        $menubookmarkdetails = $menuBookmarks->find($options)->toArray();
        $userFunctions = new UserFunctions ();
        $response = $userFunctions->arrangeMenuBookmarks($menubookmarkdetails);
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
                $finalResponse [$key] ['created_on'] = "";
            }
            $userFunctions->userId = $userid;
            $userFunctions->restaurantId = $value['restaurant_id'];
            $finalResponse [$key]['dine_and_more'] = (!$userFunctions->isRegisterWithRestaurant($userid))?1:0;
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
