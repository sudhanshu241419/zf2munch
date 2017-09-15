<?php

namespace Servers\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Predicate\Expression;

class Servers extends AbstractModel {
    
    public $id;
    public $first_name;
    public $last_name;
    public $restaurant_id;
    public $email;
    public $phone;
    public $password;
    public $code;
    public $status;
    protected $_db_table_name = 'Servers\Model\DbTable\ServersTable';
    protected $_primary_key = 'id';
    
    public function getServerDetail(array $options = array()) {
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $response = $this->find($options)->current();
        return $response;
    }
    
    public function serverRegistration() {
        $data = $this->toArray();
        $writeGateway = $this->getDbTable()->getWriteGateway();
        if (!$this->id) {
            $rowsAffected = $writeGateway->insert($data);
            // Get the last insert id and update the model accordingly
            $lastInsertId = $writeGateway->getAdapter()
                    ->getDriver()
                    ->getLastGeneratedValue();
        } else {
            $rowsAffected = $writeGateway->update($data, array(
                'id' => $this->id
            ));
            $lastInsertId = $this->id;
        }

        if ($rowsAffected >= 1) {
            $this->id = $lastInsertId;
            return $this->toArray();
        }
        return false;
    }
    public function getServerDetails($userId) {
        $select = new Select ();
        $select->from ( $this->getDbTable ()->getTableName () );
        $select->columns ( array (
                        'restaurant_id',
                        'server_name' => new Expression("CONCAT(servers.first_name,' ',servers.last_name)")
        ) );		
        $select->join ( array (
                        'rs' => 'restaurant_servers' 
        ), 'servers.code = rs.code', array (

        ), $select::JOIN_INNER );
        $select->join ( array (
                        'r' => 'restaurants' 
        ), 'rs.restaurant_id = r.id', array (
            'restaurant_name'

        ), $select::JOIN_INNER );
        $where = new Where ();
        $where->equalTo ( 'rs.user_id', $userId );
        $select->where ( $where );
        $select->limit(1);
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $serverDetails = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select )->current();
        return $serverDetails;
    }
    
}
