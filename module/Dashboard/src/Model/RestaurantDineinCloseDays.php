<?php

namespace Dashboard\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;

class RestaurantDineinCloseDays extends AbstractModel {

    public $id;
    public $restaurant_id;
    public $close_date;
    public $close_from;
    public $close_to;
    public $whole_day;
    protected $_db_table_name = 'Dashboard\Model\DbTable\RestaurantDineinCloseDaysTable';

    public function get_restaurant_day_hours($restId = 0) {
        $select = new Select ();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array(
            '*',
        ));
        $select->where(array(
            'restaurant_id' => $restId
        ));
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $records = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->toArray();
        if (!empty($records)) {
            return $records;
        } else {
            return [];
        }
    }

    public function create_restaurant_dinein_calendar($restId, $data, $date) {
        $data1['restaurant_id'] = $restId;
        $data1['close_date'] = ($data['rev_close_date'] != '') ? $data['rev_close_date'] : '0000-00-00';
        $data1['close_from'] = ($data['rev_close_from'] != '') ? $data['rev_close_from'] : '00-00';
        $data1['close_to'] = ($data['rev_close_to'] != '') ? $data['rev_close_to'] : '00-00';
        $data1['whole_day'] = ($data['rev_close_whole'] != '') ? $data['rev_close_whole'] : '0';
        $select = new Select ();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array(
            '*',
        ));
        $select->where(array(
            'restaurant_id' => $restId,'close_date' => $date,'close_from' => $data1['close_from'],'close_to' => $data1['close_to']
        ));
        var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $records = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->current();
        //$record = self::find_one(array("restaurant_id=? and close_date = ? and close_from = ? and close_to = ?", $restId, $date, $data1['close_from'], $data1['close_to']));
        if ($records) {
            $this->save($records['id'], $data1);
        } else {
            $this->save($data1);
        }
        return $data;
    }

    public function save($id = 0, $data) {
        $writeGateway = $this->getDbTable()->getWriteGateway();
        if ($id != 0) {
            $rowsAffected = $writeGateway->update($data, array(
                'id' => $id
            ));
        } else {
            $rowsAffected = $writeGateway->insert($data);
        }
        if ($rowsAffected) {
            return true;
        }
        return false;
    }

}
