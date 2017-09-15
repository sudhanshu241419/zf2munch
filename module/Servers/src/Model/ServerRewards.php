<?php

namespace Servers\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Predicate\Expression;

class ServerRewards extends AbstractModel {

    public $id;
    public $server_id;
    public $reward;
    public $earning;
    public $created_at;
    public $updated_at;
    public $status;
    protected $_db_table_name = 'Servers\Model\DbTable\ServerRewardsTable';
    protected $_primary_key = 'id';

    public function getPastWinners($startDate, $endDate) {
        $select = new Select ();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array(
            'reward',
            'earning',
            'created_at'
        ));
        $select->join(array(
            's' => 'servers'
                ), 'server_rewards.server_id = s.id', array(
            'server_name' => new Expression("CONCAT(s.first_name,' ',s.last_name)")
                ), $select::JOIN_INNER);
        $where = new Where ();
        $where->between('server_rewards.created_at', $startDate, $endDate);
        $select->where($where);
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $serversList = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->toArray();
        return $serversList;
    }

}
