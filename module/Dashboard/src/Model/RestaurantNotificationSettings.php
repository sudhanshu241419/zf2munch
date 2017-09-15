<?php

namespace Dashboard\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class RestaurantNotificationSettings extends AbstractModel {

    public $id;
    public $restaurant_id;
    public $city_id;
    public $user_name;
    public $user_password;
    public $name;
    public $email;
    public $phone;
    public $mobile;
    public $role;
    public $title;
    public $created_on;
    public $updated_at;
    public $status;
    protected $_db_table_name = 'Dashboard\Model\DbTable\RestaurantNotificationSettingsTable';
    
    static $list = array(
        'new_order_received' => '1',
        'order_cancellation' => '1',
        'new_reservation_received' => '1',
        'reservation_cancellation' => '1',
        'new_deal_coupon_purchased' => '1',
        'new_review_posted' => '1',
        'important_system_updates' => '1'
    );

    public function getNotificationDetails(array $options = array()) {
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $notification = $this->find($options)->current();
        return !empty($notification) ? $notification : self::$list;
    }

    public function update($id, $data) {
        $writeGateway = $this->getDbTable()->getWriteGateway();
        if ($id==0) {
            $rowsAffected = $writeGateway->insert($data);
        } else {
            $rowsAffected = $writeGateway->update($data, array(
                'id' => $id
            ));
        }
        if ($rowsAffected) {
            return true;
        }
        return false;
    }
    public function getRestNotificationDetails($restId) {
        $select = new Select ();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array('*'));
        $select->where->equalTo('restaurant_id', $restId);
        $details = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->toArray();
        if (!empty($details)) {
            return $details[0];
        }else{
            return '';
        } 
    }

}
