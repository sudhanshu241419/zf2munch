<?php

namespace Restaurant\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Where;

class RestaurantServer extends AbstractModel {
	public $id;
	protected $_db_table_name = 'Restaurant\Model\DbTable\RestaurantServerTable';
	
	
	public function findExistingUser($restaurant_id = 0,$user_id=0) {
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$where = new Where ();
		$where->equalTo ( 'restaurant_id', $restaurant_id );
		$where->equalTo ( 'user_id',$user_id);
		$select->where ( $where );
		$users = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select )->toArray ();
		return $users;
	}
        
        public function getServerCustomersList($code,$startDate,$endDate,$start,$limit) {
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$select->columns ( array (
				'restaurant_id',
                                'user_id',
                                'date'
		) );		
		$select->join ( array (
				'u' => 'users' 
		), 'restaurant_servers.user_id = u.id', array (
				'first_name',
                                'last_name'
		), $select::JOIN_INNER );
		$where = new Where ();
                $where->equalTo ( 'restaurant_servers.code', $code );
                $where->between('restaurant_servers.date', $startDate, $endDate);
		$select->where ( $where );
                $select->order('restaurant_servers.id DESC');
                $select->limit($limit)->offset($start);
		//var_dump($select->getSqlString($this->getPlatform('READ')));die;
		$customersList = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select )->toArray();
		return $customersList;
	}
        public function getServers($startDate,$endDate) {
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$select->columns ( array (
                    'total_customers' => new Expression('COUNT(restaurant_servers.code)'),
				'restaurant_id',
                                'code',
                                'date'
		) );		
		$select->join ( array (
				's' => 'servers' 
		), 'restaurant_servers.code = s.code and restaurant_servers.restaurant_id = s.restaurant_id', array (
                    'server_name' => new Expression("CONCAT(s.first_name,' ',s.last_name)")
		), $select::JOIN_INNER );
                $select->join ( array (
				'r' => 'restaurants' 
		), 'restaurant_servers.restaurant_id = r.id', array (
                                'restaurant_name'
		), $select::JOIN_INNER );
		$where = new Where ();
                $where->equalTo('restaurant_servers.status', 1);
                $where->between('restaurant_servers.date', $startDate, $endDate);
		$select->where ( $where );
                $select->group('restaurant_servers.code');
                $select->order('total_customers desc');
                $select->limit(3)->offset(0);
		//var_dump($select->getSqlString($this->getPlatform('READ')));die;
		$serversList = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select )->toArray();
		return $serversList;
	}
        public function gettingServerCustomers($code,$startDate,$endDate) {
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$select->columns ( array (
                    'total_customers' => new Expression('COUNT(restaurant_servers.code)'),
				'restaurant_id',
                                'code',
                                'date'
		) );		
		$select->join ( array (
				's' => 'servers' 
		), 'restaurant_servers.code = s.code', array (
                    'server_name' => new Expression("CONCAT(s.first_name,' ',s.last_name)")
		), $select::JOIN_INNER );
                $select->join ( array (
				'r' => 'restaurants' 
		), 'restaurant_servers.restaurant_id = r.id', array (
                                'restaurant_name'
		), $select::JOIN_INNER );
		$where = new Where ();
                $where->equalTo('s.code', $code);
                $where->equalTo('restaurant_servers.status', 1);
                $where->between('restaurant_servers.date', $startDate, $endDate);
                $select->group('restaurant_servers.code');
		$select->where ( $where );
                $select->limit(1);
		//var_dump($select->getSqlString($this->getPlatform('READ')));die;
		$serverData = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select )->toArray();
		return $serverData;
	}
        public function getDineAndMoreCustomers($restId,$restStartDate,$restEndDate) {
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$select->columns ( array (
				'restaurant_id',
                                'user_id',
                                'date'
		) );		
		$select->join ( array (
				'u' => 'users' 
		), 'restaurant_servers.user_id = u.id', array (
	           'first_name',
                   'last_name',
                   'email',
		), $select::JOIN_INNER );
                $select->join ( array (
				's' => 'servers' 
		), 'restaurant_servers.code = s.code and restaurant_servers.restaurant_id = s.restaurant_id', array (
                       'server_name' => new Expression("CONCAT(s.first_name,' ',s.last_name)"),
                    
		), $select::JOIN_LEFT );
		$where = new Where ();
                $where->equalTo ( 'restaurant_servers.restaurant_id', $restId );
                $where->between('restaurant_servers.date', $restStartDate, $restEndDate);
		$select->where ( $where );
                $select->order('restaurant_servers.id DESC');
		//var_dump($select->getSqlString($this->getPlatform('READ')));die;
		$customersList = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select )->toArray();
                return $customersList;
	}
    public function getRestaurantTotalCustomers($restId,$restStartDate,$restEndDate) {
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(
            'total_members' => new Expression('COUNT(id)'),
        ));
        $where = new Where ();
        $where->equalTo('restaurant_id', $restId);
        $select->where($where);
        $select->group('restaurant_id');
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $data = $this->getDbTable()
                        ->setArrayObjectPrototype('ArrayObject')
                        ->getReadGateway()
                        ->selectWith($select)->toArray();
        if (!empty($data)) {
            return $data[0]['total_members'];
        } else {
            return 0;
        }
    }
    
    public function getRestaurantTotalNewCustomers($restId,$startDate,$endDate) {
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(
            'new_members' => new Expression('COUNT(id)'),
        ));
        $where = new Where ();
        $where->equalTo('restaurant_id', $restId);
        $where->between('date', $startDate, $endDate);
        $select->where($where);
        $select->group('restaurant_id');
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $data = $this->getDbTable()
                        ->setArrayObjectPrototype('ArrayObject')
                        ->getReadGateway()
                        ->selectWith($select)->toArray();
        if (!empty($data)) {
            return $data[0]['new_members'];
        } else {
            return 0;
        }
    }
    public function getMembersIds($restId,$restStartDate,$restEndDate) {
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$select->columns ( array (
                                'user_id',
		) );		
		$where = new Where ();
                $where->equalTo ( 'restaurant_id', $restId );
                $where->between('date', $restStartDate, $restEndDate);
		$select->where ( $where );
		//var_dump($select->getSqlString($this->getPlatform('READ')));die;
		$memberIds = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select )->toArray();
		$ids = [];
                if (!empty($memberIds)) {
                foreach ($memberIds as $key => $value) {
                    $ids[] = $value['user_id'];
                }
                }else{
                    $ids = [0];
                }
                return $ids;
	}
}