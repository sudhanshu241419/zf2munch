<?php

namespace Dashboard\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class CmsSolrindexing extends AbstractModel {

    public $id;
    protected $_db_table_name = 'Dashboard\Model\DbTable\CmsSolrindexingTable';

    public function solrIndexRestaurant($restId, $restCode) {;
        $result = $this->getIndexing($restId);
        if ($result) {
            $data['updated_on'] = time();
            $data['is_indexed'] =  0;
            $this->update($result['id'],$data);
        } else {
            $data['updated_on'] = time();
            $data['restaurant_id'] = $restId;
            $data['rest_code'] = $restCode;
            $this->save($data);
        }
        return true;
    }

    public function getIndexing($restId) {
        $select = new Select ();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array('*'));
        $where = new Where ();
        $where->equalTo('restaurant_id', $restId);
        $select->where($where);
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $record = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->current();
        if (!empty($record)) {
            return $record;
        }
    }

    public function update($id = 0, $data) {
        $writeGateway = $this->getDbTable()->getWriteGateway();
        if ($id != 0) {
            $rowsAffected = $writeGateway->update($data, array(
                'id' => $id
            ));
        } else {
            $rowsAffected = $writeGateway->insert($data);
        }
        if ($rowsAffected) {
            return true;
        }
        return false;
    }

    public function save($data) {
        $writeGateway = $this->getDbTable()->getWriteGateway();
        try {
            $rowsAffected = $writeGateway->insert($data);
        } catch (\Exception $e) {
            \Zend\Debug\Debug::dump($e->__toString());
            exit;
        }
        if ($rowsAffected) {
            return true;
        }
        return false;
    }

}
