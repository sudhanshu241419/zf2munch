<?php

namespace Dashboard\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;

class RestaurantDineinCalendars extends AbstractModel {

    public $id;
    public $restaurant_id;
    public $breakfast_start_time;
    public $breakfast_end_time;
    public $lunch_start_time;
    public $lunch_end_time;
    public $dinner_start_time;
    public $dinner_end_time;
    public $breakfast_seats;
    public $lunch_seats;
    public $dinner_seats;
    public $dinningtime_small;
    public $dinningtime_large;
    public $updatedAt;
    public $status;
    protected $_db_table_name = 'Dashboard\Model\DbTable\RestaurantDineinCalendarsTable';

    public function findRestaurantDineinDetail(array $options = array()) {
        $dineinDetailObj = $this->find($options)->current();
        if ($dineinDetailObj)
            $dineinDetail = $dineinDetailObj->toArray();
        else
            $dineinDetail = array();

        return $dineinDetail;
    }

    public function getDineinTime($restaurant_id = 0) {
        $select = new Select ();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array(
            'breakfast_start_time',
            'breakfast_end_time',
            'lunch_start_time',
            'lunch_end_time',
            'dinner_start_time',
            'dinner_end_time'
        ));

        $select->where(array(
            'restaurants.id' => $restaurant_id
        ));
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $dineinTime = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select);
        return $dineinTime;
    }

    public function getDineinSeats($restaurant_id = 0) {
        $select = new Select ();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array(
            'breakfast_seats',
            'lunch_seats',
            'dinner_seats'
        ));
        $select->where(array(
            'restaurants.id' => $restaurant_id
        ));
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $seats = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select);
        return $seats;
    }

    public function get_restaurant_day_hours($restId) {
        $data = [];
        $restModel = new Restaurant();
        $restDetail = $restModel->getRestaurantDetail($restId);
        $select = new Select ();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array(
            '*',
        ));
        $select->where(array(
            'restaurant_id' => $restId
        ));
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $record = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->current();
        if ($record) {
            $data = $record;
            $data['updatedat'] = $data['updatedAt'];
            $data['updatedAt'] = date('M d, Y', strtotime($data['updatedAt']));
        } else {
            $data['restaurant_id'] = $restId;
            $data['breakfast_seats'] = 20;
            $data['lunch_seats'] = 20;
            $data['dinner_seats'] = 20;
            $data['breakfast_start_time'] = "05:00:00";
            $data['breakfast_end_time'] = "11:59:59";

            $data['lunch_start_time'] = "12:00:00";
            $data['lunch_end_time'] = "16:59:59";

            $data['dinner_start_time'] = "17:00:00";
            $data['dinner_end_time'] = "04:59:59";
            $data['status'] = '1';
            $data['dinningtime_small'] = "30";
            $data['dinningtime_large'] = "90";
            $data['updatedAt'] = date("Y-m-d H:i:s");
            $data['updatedat'] = date("Y-m-d H:i:s");
            $writeGateway = $this->getDbTable()->getWriteGateway();
            $rowsAffected = $writeGateway->insert($data);
            $data['updatedAt'] = date('M d, Y', strtotime($data['updatedAt']));
            $total_seat = $data['breakfast_seats'] + $data['lunch_seats'] + $data['dinner_seats'];
            $updateData['total_seats'] = $total_seat;
            $restModel->update($restId, $updateData);
        }
        $data['breakfast_start_time'] = date('h:i A', strtotime($data['breakfast_start_time']));
        $data['breakfast_end_time'] = date('h:i A', strtotime($data['breakfast_end_time']));

        if (date('i', strtotime($data['breakfast_end_time'])) == '59' || date('i', strtotime($data['breakfast_end_time'])) == '29') {
            $new_breakfast_end_time = strtotime('+1 minutes', strtotime($data['breakfast_end_time']));
            $data['breakfast_end_time'] = date('h:i A', $new_breakfast_end_time);
        }
        $data['lunch_start_time'] = date('h:i A', strtotime($data['lunch_start_time']));
        $data['lunch_end_time'] = date('h:i A', strtotime($data['lunch_end_time']));
        if (date('i', strtotime($data['lunch_end_time'])) == '59' || date('i', strtotime($data['lunch_end_time'])) == '29') {
            $new_lunch_end_time = strtotime('+1 minutes', strtotime($data['lunch_end_time']));
            $data['lunch_end_time'] = date('h:i A', $new_lunch_end_time);
        }
        $data['dinner_start_time'] = date('h:i A', strtotime($data['dinner_start_time']));
        $data['dinner_end_time'] = date('h:i A', strtotime($data['dinner_end_time']));
        ;
        if (date('i', strtotime($data['dinner_end_time'])) == '59' || date('i', strtotime($data['dinner_end_time'])) == '29') {
            $new_dinner_end_time = strtotime('+1 minutes', strtotime($data['dinner_end_time']));
            $data['dinner_end_time'] = date('h:i A', $new_dinner_end_time);
        }
        $data['dinein'] = $restDetail['dining'];
        return $data;
    }

    public function create_restaurant_dinein_calendar($restId, $data) {
        $select = new Select ();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array(
            '*',
        ));
        $select->where(array(
            'restaurant_id' => $restId
        ));
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $record = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->current();
        //$record = self::find_one(array("restaurant_id=?", $restaurant_id));
        $data['restaurant_id'] = $restId;
        $breakfast_start_time = strtotime($data['breakfast_start_time']);
        $data['breakfast_start_time'] = date('H:i:s', $breakfast_start_time);
        $dinner_end_time = strtotime($data['dinner_end_time']);
        $breakfast_end_time = strtotime($data['breakfast_end_time']);
        $lunch_start_time = strtotime($data['lunch_start_time']);
        $data['lunch_start_time'] = date('H:i:s', $lunch_start_time);
        $lunch_end_time = strtotime($data['lunch_end_time']);
        $dinner_start_time = strtotime($data['dinner_start_time']);
        $dinner_end_time = strtotime($data['dinner_end_time']);
        $data['dinner_start_time'] = date('H:i:s', $dinner_start_time);
        $data['breakfast_open'] = $data['breakfast_open'];
        $data['lunch_open'] = $data['lunch_open'];
        $data['dinner_open'] = $data['dinner_open'];
        //update restaurant calender
        $operation_hrs_ft = '';
        $operation_hrs = '';
        if ($data['breakfast_open'] == 1) {
            if ($breakfast_start_time < $breakfast_end_time) {
                $operation_hrs_ft = date('H:i a', $breakfast_start_time) . '-' . date('H:i a', $breakfast_end_time);
                $operation_hrs = date('H:i', $breakfast_start_time) . '-' . date('H:i', $breakfast_end_time);
            }
        }
        if ($data['lunch_open'] == 1 && $data['breakfast_open'] != 0) {
            if (($breakfast_end_time < $lunch_start_time) && ($breakfast_end_time < $lunch_end_time) && ($lunch_start_time < $lunch_end_time)) {
                if ($operation_hrs_ft != '' && $operation_hrs != '') {
                    $operation_hrs_ft .= ', ' . date('H:i a', $lunch_start_time) . '-' . date('H:i a', $lunch_end_time);
                    $operation_hrs .= ', ' . date('H:i', $lunch_start_time) . '-' . date('H:i', $lunch_end_time);
                } else {
                    $operation_hrs_ft = date('H:i a', $lunch_start_time) . '-' . date('H:i a', $lunch_end_time);
                    $operation_hrs = date('H:i', $lunch_start_time) . '-' . date('H:i', $lunch_end_time);
                }
            }
        } else if ($data['lunch_open'] == 1) {
            if ($operation_hrs_ft != '' && $operation_hrs != '') {
                $operation_hrs_ft .= ', ' . date('H:i a', $lunch_start_time) . '-' . date('H:i a', $lunch_end_time);
                $operation_hrs .= ', ' . date('H:i', $lunch_start_time) . '-' . date('H:i', $lunch_end_time);
            } else {
                $operation_hrs_ft = date('H:i a', $lunch_start_time) . '-' . date('H:i a', $lunch_end_time);
                $operation_hrs = date('H:i', $lunch_start_time) . '-' . date('H:i', $lunch_end_time);
            }
        }

        if ($data['dinner_open'] == 1 && $data['lunch_open'] != 0) {
            if (($lunch_end_time < $dinner_start_time) && ($lunch_end_time < $dinner_end_time) && ($dinner_start_time < $dinner_end_time)) {
                if ($operation_hrs_ft != '' && $operation_hrs != '') {
                    $operation_hrs_ft .= ', ' . date('H:i a', $dinner_start_time) . '-' . date('H:i a', $dinner_end_time);
                    $operation_hrs .= ', ' . date('H:i', $dinner_start_time) . '-' . date('H:i', $dinner_end_time);
                } else {
                    $operation_hrs_ft = date('H:i a', $dinner_start_time) . '-' . date('H:i a', $dinner_end_time);
                    $operation_hrs = date('H:i', $dinner_start_time) . '-' . date('H:i', $dinner_end_time);
                }
            }
        } else if ($data['dinner_open'] == 1 && $data['breakfast_open'] != 0) {
            if (($breakfast_end_time < $dinner_start_time) && ($breakfast_end_time < $dinner_end_time) && ($dinner_start_time < $dinner_end_time)) {
                if ($operation_hrs_ft != '' && $operation_hrs != '') {
                    $operation_hrs_ft .= ', ' . date('H:i a', $dinner_start_time) . '-' . date('H:i a', $dinner_end_time);
                    $operation_hrs .= ', ' . date('H:i', $dinner_start_time) . '-' . date('H:i', $dinner_end_time);
                } else {
                    $operation_hrs_ft = date('H:i a', $dinner_start_time) . '-' . date('H:i a', $dinner_end_time);
                    $operation_hrs = date('H:i', $dinner_start_time) . '-' . date('H:i', $dinner_end_time);
                }
            }
        } else if ($data['dinner_open'] == 1) {
            if ($operation_hrs_ft != '' && $operation_hrs != '') {
                $operation_hrs_ft .= ', ' . date('H:i a', $dinner_start_time) . '-' . date('H:i a', $dinner_end_time);
                $operation_hrs .= ', ' . date('H:i', $dinner_start_time) . '-' . date('H:i', $dinner_end_time);
            } else {
                $operation_hrs_ft = date('H:i a', $dinner_start_time) . '-' . date('H:i a', $dinner_end_time);
                $operation_hrs = date('H:i', $dinner_start_time) . '-' . date('H:i', $dinner_end_time);
            }
        }
        //pr($data,1);
        //pr($operation_hrs_ft,1);
        if ($breakfast_end_time == $lunch_start_time) {
            $new_breakfast_end_time = strtotime("-1 seconds", $breakfast_end_time);
            $data['breakfast_end_time'] = date('H:i:s', $new_breakfast_end_time);
        } else {
            $data['breakfast_end_time'] = date('H:i:s', $breakfast_end_time);
        }
        if ($lunch_end_time == $dinner_start_time) {
            $new_lunch_end_time = strtotime("-1 seconds", $lunch_end_time);
            $data['lunch_end_time'] = date('H:i:s', $new_lunch_end_time);
        } else {
            $data['lunch_end_time'] = date('H:i:s', $lunch_end_time);
        }
        if ($breakfast_start_time == $dinner_end_time) {
            $new_dinner_end_time = strtotime("-1 seconds", $dinner_end_time);
            $data['dinner_end_time'] = date('H:i;s', $new_dinner_end_time);
        } else {
            $data['dinner_end_time'] = date('H:i;s', $dinner_end_time);
        }
        $data['breakfast_seats'] = $data['breakfast_seats'];
        $data['lunch_seats'] = $data['lunch_seats'];
        $data['dinner_seats'] = $data['dinner_seats'];
        $data['dinningtime_small'] = $data['dinningtime_small'];
        $data['dinningtime_large'] = $data['dinningtime_large'];
        $data['updatedAt'] = date("Y-m-d H:i:s");
        $data['status'] = $data['status'];
        if ($record) {
            $this->save($record['id'], $data);
            $restModel = new Restaurant();
            $total_seat = $data['breakfast_seats'] + $data['lunch_seats'] + $data['dinner_seats'];
            $updateData['total_seats'] = $total_seat;
            $restModel->update($restId, $updateData);
        } else {
            $this->save($data);
        }
        
        $data['updatedAt'] = date('M d, Y', strtotime($data['updatedAt']));
        $data['breakfast_start_time'] = date('h:i A', strtotime($data['breakfast_start_time']));
        $data['breakfast_end_time'] = date('h:i A', strtotime($data['breakfast_end_time']));

        $data['lunch_start_time'] = date('h:i A', strtotime($data['lunch_start_time']));
        $data['lunch_end_time'] = date('h:i A', strtotime($data['lunch_end_time']));

        $data['dinner_start_time'] = date('h:i A', strtotime($data['dinner_start_time']));
        $data['dinner_end_time'] = date('h:i A', strtotime($data['dinner_end_time']));
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
