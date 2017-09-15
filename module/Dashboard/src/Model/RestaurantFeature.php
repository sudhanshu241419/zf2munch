<?php

namespace Dashboard\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Where;

class RestaurantFeature extends AbstractModel {

    public $id;
    protected $_db_table_name = 'Dashboard\Model\DbTable\RestaurantFeatureTable';

    public function get_all_cuisine_by_type() {
        $select = new Select ();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array(
            '*'
        ));
        $select->where(array(
            'status' => '1'
        ));
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $records = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->toArray();
        return $records;
    }

    public function get_restaurant_features_id($restaurant_id) {
        $features = array();
        $feature = null;
        $select = new Select ();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array(
            'feature_id'
        ));
        $select->where(array(
            'status' => '1',
            'restaurant_id' => $restaurant_id
        ));
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $record = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->toArray();

        if (!empty($record)) {
            foreach ($record as $key => $value) {
                $features[] = $value['feature_id'];
            }
            //$feature =@implode(",", $features);
        }
        return $features;
    }

    public function update_restaurant_features($features, $restId) {
        $restModel = new Restaurant();
        $restDetail = $restModel->getRestaurantDetail($restId);
        if (!empty($features)) {
            $this->update_restaurant_features_status($restId);
            foreach ($features as $value) {
                $condition = ['restaurant_id' => $restId, 'feature_id' => $value, 'status' => 0];
                $exists = $this->getRestaurantFeatures($condition);
                if ($exists) {
                    $id = $exists['id'];
                    $this->update($id, array('status' => '1'));
                } else {
                    $id = '';
                    $featureData['feature_id'] = $value;
                    $featureData['restaurant_id'] = $restId;
                    $featureData['status'] = '1';
                    $this->update($id, $featureData);
                }
            }
            $indexingModel = new CmsSolrindexing();
            $indexingModel->solrIndexRestaurant($restId, $restDetail['rest_code']);
        }
        return ["status"=>"success"];
    }

    public function update($id = 0, $data) {
        $writeGateway = $this->getDbTable()->getWriteGateway();
        if ($id) {
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

    public function getRestaurantFeatures($condition) {
        $select = new Select ();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array('*'));
        $where = new Where ();
        $where->equalTo('restaurant_id', $condition['restaurant_id']);
        $where->equalTo('feature_id', $condition['feature_id']);
        $where->equalTo('status', $condition['status']);
        $select->where($where);
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $record = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->current();
        if (!empty($record)) {
            return $record;
        }
    }

    public function update_restaurant_features_status($restId) {
        $select = new Select ();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array('*'));
        $where = new Where ();
        $where->equalTo('restaurant_id', $restId);
        $where->equalTo('status', 1);
        $select->where($where);
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $record = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->toArray();
        if (!empty($record)) {
            foreach ($record as $value) {
                $this->update($value['id'], array('status' => '0'));
            }
        }
        return true;
    }

}
