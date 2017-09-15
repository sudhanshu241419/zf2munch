<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\UserFunctions;
use Restaurant\Model\MenuBookmark;
use Zend\Db\Sql\Predicate\Expression;

class WebMenuBookmarksCountController extends AbstractRestfulController {

    public function getList() {
        $menuBookmarksModel = new MenuBookmark();
        $menuBookmarksModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $session = $this->getUserSession();
        $userId = $session->getUserId();
        $joins[] = array(
            'name' => array(
                'r' => 'restaurants'
            ),
            'on' => new Expression("(r.id = menu_bookmarks.restaurant_id)"),
            'columns' => array(
                'restaurant_name'
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
            'where' => array(
                'user_id' => $userId
            ),
            'joins' => $joins
        );
        $bookmarks = $menuBookmarksModel->find($options)->toArray();
        $userFunctions = new UserFunctions ();
        $response = $userFunctions->arrangeMenuBookmarks($bookmarks);
        $total = count($response);
        $response = $this->getCounts($response);
        $response ['total'] = $total;
        return $response;
    }

    private function getCounts($response) {
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
            'want_it_count' => $wantIt,
            'tried_it_count' => $triedIt
        );
    }

}
