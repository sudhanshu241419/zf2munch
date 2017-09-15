<?php

namespace Dashboard\Model;

use MCommons\Model\AbstractModel;
use MCommons\StaticOptions;

class Token extends AbstractModel {

    public $id;
    public $token;
    public $dashboard_details;
    public $dashboard_id;
    public $ttl;
    public $created_at;
    public $last_update_timestamp;
    protected $_db_table_name = 'Dashboard\Model\DbTable\AuthTable';
    protected $_redisCache = false;

    public function __construct() {
        $this->_redisCache = StaticOptions::getRedisCache();
    }

    /**
     * Return false if save was not successfull else return the updated object
     *
     * @return \Auth\Model\Token boolean
     */
    public function save() {
        $data = $this->toArray();
        if ($this->_redisCache) {
            $this->_redisCache->setItem($this->token, $data);
            $data['id'] = $this->token;
            return $this;
        }
        $writeGateway = $this->getDbTable()->getWriteGateway();
        if (!$this->id) {
            $rowsAffected = $writeGateway->insert($data);
        } else {
            $rowsAffected = $writeGateway->update($data, array(
                'id' => $this->id
            ));
        }
        // Get the last insert id and update the model accordingly
        $lastInsertId = $writeGateway->getAdapter()->getDriver()->getLastGeneratedValue();
        if ($rowsAffected >= 1) {
            $this->id = $lastInsertId;
            return $this;
        }
        return false;
    }

    public function delete() {
        if ($this->_redisCache && $this->_redisCache->hasItem($this->token)) {
            $data = [];
            $this->_redisCache->setItem($this->token, $data);
            return true;
        }
        
        if (!$this->token) {
            throw new \Exception("Invalid token provided", 500);
        } else {
            $data = array('dashboard_id' => NULL, 'dashboard_details' => NULL);
            $writeGateway = $this->getDbTable()->getWriteGateway();
            $rowsAffected = $writeGateway->update($data, array(
                'token' => $this->token
            ));
        }
        if ($rowsAffected) {
            return true;
        }
    }

    public function findToken($token) {
        if ($this->_redisCache) {
            if ($this->_redisCache->hasItem($token)) {
                $data = $this->_redisCache->getItem($token);
                if (empty($data)) {
                    $dashboardData = [];
                    $this->dashboard_id = '';
                    $this->token = $token;
                    $this->ttl = 315360000;
                    $this->created_at = date('Y-m-d H:i');
                    $this->dashboard_details = @serialize($dashboardData);
                    $this->last_update_timestamp = time();
                    $this->save();
                    $data = array('dashboard_details' => [], 'dashboard_id' => '');
                }
//                pr($data,1);
//                $data['dashboard_details'] = @serialize($data['dashboard_details']);
//                $this->exchangeArray($this->_redisCache->getItem($token));
                return $data;
            }
            return false;
        }

        return $this->find(array(
                    'where' => array(
                        'token' => $token
                    )
                ))->current()->toArray();
    }

    public function findExpireTimeToken($token) {
        if ($this->_redisCache) {
            if ($this->_redisCache->hasItem($token)) {
                $data = $this->_redisCache->getItem($token);
                $data['dashboard_details'] = @serialize($data['dashboard_details']);
                $tokenExpireTime = (int) $data['ttl'] + (int) $data['last_update_timestamp'];
                return $tokenExpireTime;
            }
            return false;
        }
        return false;
    }

    public function getDashboardDetails($token) {
        $dashbordD = $this->find(array('columns' => array('id', 'dashboard_id', 'dashboard_details'),
                    'where' => array(
                        'token' => $token
                    )
                ))->current();
        return $dashbordD;
    }

}
