<?php

namespace Restaurant\Model;

use MCommons\Model\AbstractModel;

class UserPromocodes extends AbstractModel {

    public $id;
    public $promo_id;
    public $order_id;
    public $reedemed;
    public $user_id;
    protected $_db_table_name = 'Restaurant\Model\DbTable\UserPromocodesTable';
    protected $_primary_key = 'id';

    public function update($data) {
        $writeGateway = $this->getDbTable()->getWriteGateway();
        $rowsAffected = $writeGateway->update($data, array(
            'id' => $this->id
        ));
        return true;
    }

    public function insert($data) {
       
        $writeGateway = $this->getDbTable()->getWriteGateway();
        $rowsAffected = $writeGateway->insert($data);
               
        if ($rowsAffected) {
            return true;
        } else {
            return false;
        }
    }

    public function getUserAssignPromo() {
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $joins_promo = array();
        $joins_promo [] = array(
            'name' => array(
                'p' => 'promocodes'
            ),
            'on' => 'user_promocodes.promo_id = p.id',
            'columns' => array(
                'promo_id' => 'id',
                'promocodeType'
            ),
            'type' => 'inner'
        );
        $options = array(
            'columns' => array(
                'user_id'
            ),
            'where' => array('p.promocodeType' => 2), //array('users.city_id' => 18848),  
            'joins' => $joins_promo,
            'group' => 'user_promocodes.user_id'
        );
        $userDetail = $this->find($options)->toArray();
        return $userDetail;
    }
    
    public function getPromocodeOfUser($userId){
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $joins_promo = array();
        $joins_promo [] = array(
            'name' => array(
                'p' => 'promocodes'
            ),
            'on' => 'user_promocodes.promo_id = p.id',
            'columns' => array(
                'promo_id' => 'id',
                'promocodeType'
            ),
            'type' => 'inner'
        );
        $options = array(
            'columns' => array(
                'user_id'
            ),
            'where' => array('user_promocodes.user_id' => 2,'user_promocodes.user_id' => $userId), //array('users.city_id' => 18848),  
            'joins' => $joins_promo,            
        );
        
        if($this->find($options)->toArray()){
            return false;
        }
        return true;
    }

}
