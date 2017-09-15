<?php

namespace Dashboard\Model;

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
    protected $_db_table_name = 'Dashboard\Model\DbTable\ServersTable';
    protected $_primary_key = 'id';

    public function getServerName($code, $restId) {
        $select = new Select();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array('first_name', 'last_name'));
        $where = new Where();
        $where->equalTo('code', $code);
        $where->equalTo('restaurant_id', $restId);
        $select->where($where);
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $server = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->current();
        $server = empty($server) ? '' : ucfirst($server['first_name']) . ' ' . ucfirst($server['last_name']);
        return $server;
    }

}
