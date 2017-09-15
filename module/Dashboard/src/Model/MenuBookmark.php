<?php

namespace Dashboard\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class MenuBookmark extends AbstractModel {

    public $id;
    public $menu_id;
    public $restaurant_id;
    public $menu_name;
    public $user_id;
    public $type;
    public $created_on;
    protected $_db_table_name = 'Dashboard\Model\DbTable\MenuBookmarkTable';
    protected $_primary_key = 'id';

    public function getGuestFavouriteItems($id, $restId) {
        $favourites = [];
        $select = new Select();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array('*'));
        $where = new Where ();
        $where->equalTo('user_id', $id);
        $where->equalTo('restaurant_id', $restId);
        $select->where($where);
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $bookmark = $this->getDbTable()
                        ->setArrayObjectPrototype('ArrayObject')
                        ->getReadGateway()
                        ->selectWith($select)->toArray();
        if (!empty($bookmark)) {
            foreach ($bookmark as $value) {
                $favourites[] = " " . $value['menu_name'];
            }
            $favourites = implode(',', $favourites);
        }
        return $favourites;
    }
}
