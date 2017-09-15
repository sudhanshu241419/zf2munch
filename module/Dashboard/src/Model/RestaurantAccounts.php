<?php

namespace Dashboard\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;

class RestaurantAccounts extends AbstractModel {

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
    protected $_db_table_name = 'Dashboard\Model\DbTable\RestaurantAccountTable';
    const O = 'Owner';
    const M  = 'Manager'; 
    const A = 'Admin';

    public function getRestaurantAccountDetail(array $options = array()) {
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        return $this->find($options)->current();
    }

    public function authRestaurantAccount($data) {
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array('id', 'user_name', 'email', 'restaurant_id', 'city_id', 'created_on'));
        $where = new Where();
        $where->NEST->equalTo('status', 1)->AND->equalTo('user_password', $data['dashboard_password'])->UNNEST->AND->NEST->equalTo('email', $data['dashboard_username'])->OR->equalTo('user_name', $data['dashboard_username'])->UNNEST;
        $select->where($where);
        //var_dump($select->getSqlString($this->getPlatform('READ'))); die();
        $restAccData = $this->getDbTable()
                ->setArrayObjectPrototype('ArrayObject')
                ->getReadGateway()
                ->selectWith($select)
                ->toArray();
        return $restAccData;
    }

    public function fetchCount($rest_id = 0, $email = "", $password = "") {
        $options = array(
            'columns' => array('count' => new \Zend\Db\Sql\Expression('COUNT(*)')),
            'where' => array('restaurant_id' => $rest_id, 'email' => $email, 'user_password' => $password)
        );
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        return $this->find($options)->current()->getArrayCopy();
    }

    public function checkRestaurantForMail($restaurantId, $flag = NULL) {
        return false;
        //    	if (!empty($restaurantId)){
        //    		$record = $this->getRestaurantAccountDetail(array(
        //    				'columns' => array(
        //    						'status',
        //    				        'email'
        //    
        //    				),
        //    				'where' => array(
        //    						'restaurant_id' => $restaurantId
        //    				)
        //    		));
        //    		if($record['status']==1 && !empty($record['email'])){
        //    			$restaurantSetting = new RestaurantNotificationSettings();
        //    			$sendMail = $restaurantSetting->getRestaurantSettingStatus($restaurantId,$flag);
        //    			return  $sendMail;
        //    		}
        //    	}
    }

    public function create() {
        $data = $this->toArray();
        $writeGateway = $this->getDbTable()->getWriteGateway();
        if (!$this->id) {
            $rowsAffected = $writeGateway->insert($data);
        } else {
            $rowsAffected = $writeGateway->update($data, array(
                'id' => $this->id
            ));
        }

        $lastInsertId = $writeGateway->getAdapter()->getDriver()->getLastGeneratedValue();

        if ($rowsAffected >= 1) {
            $this->id = $lastInsertId;
            return $this->toArray();
        }
        return false;
    }

    public function checkeScondaryAccounts($restId) {
        $select = new Select ();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array(
            'total' => new Expression('COUNT(id)')
        ));
        $select->where->equalTo('restaurant_id', $restId);
        $total = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->current();
        if (!empty($total)) {
            return ($total['total'] > 1) ? 'yes' : 'no';
        } else {
            return 'no';
        }
    }

    public function checkeScondaryAccountDetails($restId) {
        $select = new Select ();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array('*'));
        $select->where->equalTo('restaurant_id', $restId);
        $select->where->equalTo('role', 'o');
        $details = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->toArray();
        if (!empty($details)) {
            return $details;
        } else {
            return $details = [];
        }
    }

    public function update($id = 0, $data) {
        $writeGateway = $this->getDbTable()->getWriteGateway();
        if ($id > 0) {
            $rowsAffected = $writeGateway->update($data, array(
                'id' => $id
            ));
        }
        if ($rowsAffected) {
            return true;
        }
        return false;
    }

    public function getRestAccountDetail($restId) {
        $select = new Select ();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array('*'));
        $select->where->equalTo('restaurant_id', $restId);
        $details = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->toArray();
        if (!empty($details)) {
            return $details[0];
        }
    }

    public function update_details($data, $restId) {
        $restModel = new Restaurant();
        $restDetail = $restModel->getRestaurantDetail($restId);
        $restAccountDetails = $this->getRestAccountDetail($restId);
        if (!empty($restAccountDetails)) {
            if ($data['contactperson']) {
                $update['name'] = $data['contactperson'];
            }

            if (!empty($data['title']) && $data['title'] == self::O) {
                $update['role'] = 'o';
            }
            if (!empty($data['title']) && $data['title'] == self::M) {
                $update['role'] = 'm';
            }
            if (!empty($data['title']) && $data['title'] == self::A) {
                $update['role'] = 'a';
            }
            $update['memail'] = $data['memail'];
            $this->update($restAccountDetails['id'] , $update);
        }
        if ($restDetail) {
            $indexingModel = new CmsSolrindexing();
            $indexingModel->solrIndexRestaurant($restId, $restDetail['rest_code']);
            return ["status"=>"success"];
        } else {
            return ["status"=>"error"];
        }
    }
    public function checkEmailExist($email) {
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(
            'id','restaurant_id','email',
        ));
        $select->join(array(
            'r' => 'restaurants'
                ), 'r.id = restaurant_accounts.restaurant_id', array(
            'restaurant_name',            
                ), $select::JOIN_INNER);
        $where = new Where ();
        $where->equalTo('restaurant_accounts.email', $email);
        
        $select->where($where);
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $response = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->current();
        if (!empty($response)) {
            return $response;
        } else {
            return false;
        }
    }

}
