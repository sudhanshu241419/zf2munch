<?php

namespace Dashboard\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class UserMenuReview extends AbstractModel {

    public $id;
    public $user_review_id;
    public $menu_id;
    public $image_name;
    public $liked;
    protected $_db_table_name = 'Dashboard\Model\DbTable\UserMenuReviewTable';

    public function getUserReviewImageByorder($reviewId, $reviewFor, $restId = null) {
        $img = array();
        if ($reviewFor == 'Dine-In') {
            $image = $this->getUserMenuReviews($reviewId, 0);
            return empty($image) ? array() : USER_REVIEW_IMAGE . $restId . '/' . $image[0]['image_name'];
        } else {
            $records = $image = $this->getUserMenuReviews($reviewId, 0);
            foreach ($records as $key => $value) {
                if ($value['image_name'])
                    $img[] = USER_REVIEW_IMAGE . $restId . '/menu/' . $value['image_name'];
            }
            return $img;
        }
    }

    public function getUserMenuReviews($reviewId, $return) {
        $output = array();
        $select = new Select();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array('*'));
        $where = new Where ();
        $where->equalTo('user_review_id', $reviewId);
        if ($return == 1) {
            $where->isNull('menu_id');
        }
        $select->where($where);
        //var_dump($select->getSqlString($this->getPlatform('READ'))); die();
        if ($return == 1) {
            $output = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->current();
        } else {
            $output = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->toArray();
        }
        if (!empty($output)) {
            return $output;
        }
        return $output;
    }

    public function getMenusReview($id, $restCode) {
        $output = array();
        $select = new Select();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array('*'));
        $where = new Where ();
        $where->equalTo('user_review_id', $id);
        $where->isNotNull('menu_id');
        $select->where($where);
        //var_dump($select->getSqlString($this->getPlatform('READ'))); die();
        $output = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->toArray();
        $menuModel = new Menu();
        if (!empty($output)) {
            $data = array();
            foreach ($output as $key => $value) {             
                if ($value['menu_id'] != null) {
                    $menuName = $menuModel->getMenuDetail($value['menu_id']);
                    $value['menu_name'] = ($menuName) ? $menuName['item_name'] : '';
                    if (!empty($value['image_name'])){
                        $value['image_name'] = USER_REVIEW_IMAGE . strtolower($restCode) . '/reviews/' . $value['image_name'];
                    }else{
                        $value['image_name'] = "";
                    }
                    $data[] = $value;
                }
            }
        }
        return empty($output) ? array() : $data;
    }

}
