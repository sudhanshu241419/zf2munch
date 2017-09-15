<?php

namespace Restaurant\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;

class EmailSent extends AbstractModel {

    public $id;
    public $restaurant_id;
    public $total_sent;
    public $total_opened;
    public $total_clicked;
    public $created_on;
    protected $_db_table_name = 'Restaurant\Model\DbTable\EmailSentTable';
    protected $_primary_key = 'id';

    public function getEmailDetails($restId, $restStartDate, $restEndDate) {
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(
            'total_sent'=> new Expression('SUM(total_sent)'),
            'total_opened'=> new Expression('SUM(total_opened)'),
            'total_clicked'=> new Expression('SUM(total_clicked)')
                )
        );
        $where = new Where();
        $where->equalTo('restaurant_id', $restId);
        $where->between('created_on', $restStartDate, $restEndDate);
        $select->where($where);
        //var_dump($select->getSqlString($this->getPlatform('READ')));
        $emailData = $this->getDbTable()
                ->setArrayObjectPrototype('ArrayObject')
                ->getReadGateway()
                ->selectWith($select)
                ->current();
        return $emailData;
    }

}
