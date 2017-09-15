<?php

namespace Dashboard\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class OwnerResponse extends AbstractModel {

    public $id;
    public $review_id;
    public $response;
    public $response_date;
    protected $_db_table_name = 'Dashboard\Model\DbTable\OwnerResponseTable';

    public function addResponse($data = array()) {
        $writeGateway = $this->getDbTable()->getWriteGateway();
        $rowsAffected = $writeGateway->insert($this->toArray());
        $lastInsertId = $writeGateway->getAdapter()
                ->getDriver()
                ->getLastGeneratedValue();
        if ($rowsAffected >= 1) {
            $this->id = $lastInsertId;
            return $this->toArray();
        }
    }
    public function getOwnerResponce($reviewId) {
        $output = array();
        $select = new Select();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array('*'));
        $where = new Where ();
        $where->equalTo('review_id', $reviewId);
        $select->where($where);
        //var_dump($select->getSqlString($this->getPlatform('READ'))); die();
        $output = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->toArray();
        if (!empty($output)) {
            return $output;
        }
        return $output;
    }
    public function save($data) {
        $writeGateway = $this->getDbTable()->getWriteGateway();
        $rowsAffected = $writeGateway->insert($data);
        if ($rowsAffected) {
            return true;
        }
        return false;
    }

}
