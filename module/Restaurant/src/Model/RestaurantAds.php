<?php

namespace Restaurant\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Where;

class RestaurantAds extends AbstractModel {

    public $id;
    public $restaurant_id;
    public $ad_id;
    public $ad_text;
    public $keywords;
    public $start_date;
    public $end_datet;
    public $status;
    protected $_db_table_name = 'Restaurant\Model\DbTable\RestaurantAdsTable';

    public function getRestaurantAds($restaurant_id, $keyword) {
        $select = new Select();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array('ad_id', 'ad_text'));
        $select->limit(1);
        $where = New Where();
        $where->equalTo('restaurant_id', $restaurant_id);
        //$where->like('keywords', '%'.$keyword.'%');
        $select->where($where);
        //pr($select->getSqlString($this->getPlatform()),1);
        $ads = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->toArray();
        return $ads;
    }

}
