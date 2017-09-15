<?php

namespace Dashboard\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Where;

class RestaurantSeats extends AbstractModel {

    public $id;
    public $restaurant_id;
    public $calendar_day;
    public $open_time;
    public $close_time;
    public $breakfast_start_time;
    public $breakfast_end_time;
    public $lunch_start_time;
    public $lunch_end_time;
    public $dinner_start_time;
    public $dinner_end_time;
    public $status;
    public $takeout_open;
    public $operation_hours;
    public $operation_hrs_ft;
    
    protected $_db_table_name = 'Dashboard\Model\DbTable\RestaurantSeatsTable';
    
    public function get_reserved_seats_with_slots($restId, $date) {
        $output = array();
        $select = new Select();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array(
            '*'
        ));
        $where = new Where ();
        $where->equalTo('restaurant_id', $restId);
        $where->like('start_time', '%' . $date . '%');
        $select->where($where);
        //var_dump($select->getSqlString($this->getPlatform('READ'))); die();
        $output = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->current();
        if (!empty($output)) {
            return $output;
        }
        return $output;
    }

}
