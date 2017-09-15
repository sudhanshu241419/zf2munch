<?php

namespace Dashboard\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Where;

class RestaurantCalendars extends AbstractModel {

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
    protected $_db_table_name = 'Dashboard\Model\DbTable\RestaurantCalendarsTable';
    static $daysMapping = array(
        'mo' => 'mon',
        'tu' => 'tue',
        'we' => 'wed',
        'th' => 'thu',
        'fr' => 'fri',
        'sa' => 'sat',
        'su' => 'sun'
    );

    public function get_restaurent_open_and_close_time($restId, $day) {
        $output = array();
        $select = new Select();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array(
            'open_time',
            'close_time'
        ));
        $where = new Where ();
        $where->equalTo('restaurant_id', $restId);
        $where->equalTo('calendar_day', $day);
        $select->where($where);
        //var_dump($select->getSqlString($this->getPlatform('READ'))); die();
        $output = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->current();
        if (!empty($output)) {
            return $output;
        }
        return $output;
    }

    public function getRestaurantOpeningHours($restId) {
        $data = [];
        $select = new Select();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array('*'));
        $where = new Where ();
        $where->equalTo('restaurant_id', $restId);
        $select->where($where);
        //var_dump($select->getSqlString($this->getPlatform('READ'))); die();
        $record = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->toArray();
        if (!empty($record)) {
            foreach ($record as $key => $value) {
                $value['days'] = $value['calendar_day'];
                if (isset(self::$daysMapping[$value['calendar_day']])) {
                    $value['calendar_day'] = ucfirst(self::$daysMapping[$value['calendar_day']]);
                }
                $value['breakfast_start_time'] = date('h:i A', strtotime($value['breakfast_start_time']));
                $value['breakfast_end_time'] = date('h:i A', strtotime($value['breakfast_end_time']));
                $value['lunch_start_time'] = date('h:i A', strtotime($value['lunch_start_time']));
                $value['lunch_end_time'] = date('h:i A', strtotime($value['lunch_end_time']));
                $value['dinner_start_time'] = date('h:i A', strtotime($value['dinner_start_time']));
                $value['dinner_end_time'] = date('h:i A', strtotime($value['dinner_end_time']));
                $value['open_time'] = date('h:i A', strtotime($value['open_time']));
                $value['close_time'] = date('h:i A', strtotime($value['close_time']));
                $value['takeout_status'] = $value['takeout_open'];
                $value['takeout_open'] = explode(',', $value['operation_hrs_ft']);
                $data[$key] = $value;
            }
        }
        return $data;
    }

    public function updateRestaurantCalendar($data, $restId) {
        $weekdays = array('mo', 'tu', 'we', 'th', 'fr', 'sa', 'su');
        foreach ($weekdays as $day) {
            $updatedata = array();
            $operation_hrs_ft = '';
            $operation_hrs = '';
            $open = '';
            $close = '';
            $updatedata['restaurant_id'] = $restId;
            $updatedata['calendar_day'] = $day;
            $updatedata['breakfast_start_time'] = date('H:i:s', strtotime($data['breakfast_start_time_' . $day]));
            $updatedata['breakfast_end_time'] = date('H:i:s', strtotime($data['breakfast_end_time_' . $day]));
            $updatedata['lunch_start_time'] = date('H:i:s', strtotime($data['lunch_start_time_' . $day]));
            $updatedata['lunch_end_time'] = date('H:i:s', strtotime($data['lunch_end_time_' . $day]));
            $updatedata['dinner_start_time'] = date('H:i:s', strtotime($data['dinner_start_time_' . $day]));
            $updatedata['dinner_end_time'] = date('H:i:s', strtotime($data['dinner_end_time_' . $day]));
            if (isset($data['delivery_status_' . $day]) && $data['delivery_status_' . $day] != '') {
                $updatedata['status'] = $data['delivery_status_' . $day];
            } else {
                $updatedata['status'] = 1;
            }
            if (isset($data['takeout_status_' . $day]) && $data['takeout_status_' . $day] != '') {
                $updatedata['takeout_open'] = $data['takeout_status_' . $day];
            } else {
                $updatedata['takeout_open'] = 1;
            }

            if (isset($data['takeout_open_' . $day]) && !empty($data['takeout_open_' . $day])) {
                $openTakeOpenTime = explode(',', $data['takeout_open_' . $day]);
                $openTakeCloseTime = explode(',', $data['takeout_close_' . $day]);
                foreach ($openTakeOpenTime as $key => $val) {
                    if ($key > 0) {
                        if (!empty($val)) {
                            $operation_hrs_ft .= ', ' . $val . ' - ' . $openTakeCloseTime[$key];
                            $operation_hrs .= ',' . date('H:i', strtotime($val)) . '-' . date('H:i', strtotime($openTakeCloseTime[$key]));
                            $close = date('H:i:s', strtotime($openTakeCloseTime[$key]));
                        } else {
                            $operation_hrs_ft = '';
                            $operation_hrs = '';
                            $open = '00:00:00';
                            $close = '00:00:00';
                        }
                    } else {
                        if (!empty($val)) {
                            $operation_hrs_ft = $val . ' - ' . $openTakeCloseTime[$key];
                            $operation_hrs = date('H:i', strtotime($val)) . '-' . date('H:i', strtotime($openTakeCloseTime[$key]));
                            $open = date('H:i:s', strtotime($val));
                            $close = date('H:i:s', strtotime($openTakeCloseTime[$key]));
                        } else {
                            $operation_hrs_ft = '';
                            $operation_hrs = '';
                            $open = '00:00:00';
                            $close = '00:00:00';
                        }
                    }
                }
            } else {
                $open = date('H:i:s', strtotime($data['breakfast_start_time_' . $day]));
                $close = date('H:i:s', strtotime($data['dinner_end_time_' . $day]));
                $operation_hrs_ft = $data['breakfast_start_time_' . $day] . ' - ' . $data['dinner_end_time_' . $day];
                $operation_hrs = date('H:i', strtotime($data['breakfast_start_time_' . $day])) . '-' . date('H:i', strtotime($data['dinner_end_time_' . $day]));
            }
            $updatedata['operation_hrs_ft'] = $operation_hrs_ft;
            $updatedata['operation_hours'] = $operation_hrs;
            $updatedata['open_time'] = $open;
            $updatedata['close_time'] = $close;
            $updatedata['operation_hrs_ft'] = $this->update($updatedata);
        }
        return true;
    }

    public function update($data) {
        $select = new Select();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array(
            'id',
        ));
        $where = new Where ();
        $where->equalTo('restaurant_id', $data['restaurant_id']);
        $where->equalTo('calendar_day', $data['calendar_day']);
        $select->where($where);
        //var_dump($select->getSqlString($this->getPlatform('READ'))); die();
        $record = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->current();
        if (!empty($record)) {
            $record = $this->save($record['id'], $data);
            return $record;
        }
        return false;
    }

    public function save($id = 0, $data) {
        $writeGateway = $this->getDbTable()->getWriteGateway();
        if ($id != 0) {
            $rowsAffected = $writeGateway->update($data, array(
                'id' => $id
            ));
        }
        if ($rowsAffected) {
            return true;
        }
        return false;
    }

}
