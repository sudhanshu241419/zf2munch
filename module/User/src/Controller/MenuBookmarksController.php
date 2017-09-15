<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use Restaurant\Model\MenuBookmark;
use User\UserFunctions;
use Zend\Db\Sql\Predicate\Expression;

class MenuBookmarksController extends AbstractRestfulController {

    const FORCE_LOGIN = true;

    public function getList() {
        $queryParams = $this->getRequest()->getQuery()->toArray();
        $session = $this->getUserSession();
        $userId = $session->getUserId();
        $friendId = $this->getQueryParams('friendid',false);
        if($friendId){
            $userId = $friendId;
        }
        $menuBookmarks = new MenuBookmark ();
        $menuBookmarks->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $order = array(
            'menu_bookmarks.created_on' => 'desc'
        );
//        if (isset($queryParams ['sort'])) {
//            if ($queryParams ['sort'] == 'date') {
//                $order = array(
//                    'created_on' => 'asc'
//                );
//            } elseif ($queryParams ['sort'] == 'alphabetical') {
//                $order = array(
//                    'menu_name' => 'asc'
//                );
//            }
//        }
        $joins[] = array(
            'name' => array(
                'r' => 'restaurants'
            ),
            'on' => new Expression("(r.id = menu_bookmarks.restaurant_id)"),
            'columns' => array(
                'restaurant_name',
                'closed',
                'inactive'
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
                'user_id',
                'restaurant_id',
                'menu_id',
                'menu_name',
                'type',
                'created_on'
            ),
            'where' => array(
                'user_id' => $userId
            ),   
            'order'=>$order,
            'joins' => $joins,
            'group'=>array('menu_id','user_id','type')
        );

        $menubookmarkdetails = $menuBookmarks->find($options)->toArray();
        $userFunctions = new UserFunctions ();
        $response = $userFunctions->arrangeMenuBookmarks($menubookmarkdetails);
        $count = $this->addCountToResponse($response);
// 		query need to be optimized
        $limit = $this->getQueryParams('limit', SHOW_PER_PAGE);
        $page = $this->getQueryParams('page', 1);
        $offset = 0;
        if ($page > 0) {
            $page = ($page < 1) ? 1 : $page;
            $offset = ($page - 1) * ($limit);
        }
        $totalBookmark = count($response);
        $response = array_slice($response, $offset, $limit);
        $finalResponse['bookmarks'] = $response;
        $finalResponse['count'] = $count;
        $finalResponse['total_bookmark'] = $totalBookmark;
        return $finalResponse;
    }

    private function addCountToResponse($response) {
        $lovedIt = 0;
        $wantIt = 0;
        $triedIt = 0;
        foreach ($response as $single) {
            if ($single ['loved_it']) {
                $lovedIt ++;
            }
            if ($single ['want_it']) {
                $wantIt ++;
            }
            if ($single ['tried_it']) {
                $triedIt ++;
            }
        }
        return array(
            'loved_it_count' => $lovedIt,
            'crave_it_count' => $wantIt,
            'tried_it_count' => $triedIt
        );
    }

}
