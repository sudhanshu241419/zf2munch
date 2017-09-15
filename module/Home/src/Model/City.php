<?php

namespace Home\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;

class City extends AbstractModel {

    public $id;
    public $neighbouring;
    public $state_id;
    public $country_id;
    public $city_name;
    public $state_code;
    public $latitude;
    public $longitude;
    public $sales_tax;
    public $status;
    public $time_zone;
    protected $_db_table_name = 'Home\Model\DbTable\CityTable';
    protected $_primary_key = 'id';

    public function citySearch($options) {
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        return $this->find($options)->toArray();
    }

    public function cityDetails($city_id) {
        $options = array(
            'columns' => array(
                'id',
                'city_name',
                'state_code',
                'latitude',
                'longitude',
                'time_zone',
                'neighbouring'
            ),
            'where' => array(
                'id' => $city_id
            )
        );
        return $this->find($options)->toArray();
    }

    public function cityAndStateDetails($city_id) {
        $select = new Select ();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array(
            'city_id' => 'id',
            'nbd_cities' => 'neighbouring',
            'latitude' => 'latitude',
            'longitude' => 'longitude',
            'city_name' => 'city_name',
            'locality' => 'locality',
            'is_browse_only'
        ));
        $select->join(array(
            's' => 'states'
                ), 'cities.state_id = s.id', array(
            'state_name' => 'state',
            'state_code'
                ), $select::JOIN_INNER);
        $select->where(array(
            'cities.id' => $city_id
        ));
        $readGateway = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway();
        $cityDetails = $readGateway->selectWith($select)->current()->getArrayCopy();
        return $cityDetails;
    }
    
     public function getCityDetailsforSeoModule() {
        $select = new Select ();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array(
            'city_id' => 'id',
            'nbd_cities' => 'neighbouring',
            'latitude' => 'latitude',
            'longitude' => 'longitude',
            'city_name' => 'city_name',
            'locality' => 'locality',
            
        ));
        $select->join(array(
            's' => 'states'
                ), 'cities.state_id = s.id', array(
            'state_name' => 'state',
            'state_code'
                ), $select::JOIN_INNER);
        $select->join(array(
            'c' => 'countries'
        ),'cities.country_Id=c.id', array(
            'country_name',
            'country_short_name'
        ), $select::JOIN_INNER);
        $select->where(array(
            'cities.seo' => '1'
        ));
        $readGateway = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway();
        $cityDetails = $readGateway->selectWith($select)->toArray();
        return $cityDetails;
    }

    public function getCity(array $options = array()) {
        return $this->find($options);
    }

    public function fetchCityIdByOptions($options) {
        $select = new Select ();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array(
            'city_id' => 'id',
            'state_id',
            'city_name'
        ));
        $select->join(array(
            's' => 'states'
                ), 'cities.state_id = s.id', array(
            'country_id',
            'state_name' => 'state',
            'state_code'
                ), $select::JOIN_LEFT);
        $select->join(array(
            'c' => 'countries'
                ), 's.country_id = c.id', array(
            'country_name',
            'country_code' => 'country_short_name'
                ), $select::JOIN_LEFT);
        $select->where($options);
        $readGateway = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway();
        $cityDetails = $readGateway->selectWith($select)->current();
        if ($cityDetails) {
            return $cityDetails->getArrayCopy();
        }
        return array();
    }

    public function fetchCityDetails($city_id = 0) {
        $select = new Select ();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array(
            'state_id',
            'city_name'
        ));
        $select->join(array(
            's' => 'states'
                ), 'cities.state_id = s.id', array(
            'country_id',
            'state_name' => 'state',
            'state_code'
                ), $select::JOIN_LEFT);
        $select->join(array(
            'c' => 'countries'
                ), 's.country_id = c.id', array(
            'country_name',
            'country_code' => 'country_short_name'
                ), $select::JOIN_LEFT);
        $select->where(array(
            'cities.id' => $city_id
        ));
        $readGateway = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway();
        $cityDetails = $readGateway->selectWith($select)->current();
        if ($cityDetails) {           
            return $cityDetails->getArrayCopy ();;
        }
        return array();
    }
    
    public function getCityCurrentDateTime($city_id) {
        try {
            $options = array(
                'columns' => array(
                    'time_zone'
                ),
                'where' => array(
                    'id' => $city_id
                )
            );
            $result = $this->find($options)->toArray();
            $date = new \DateTime("now", new \DateTimeZone($result[0]['time_zone']));
            return $date->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            return 'error';
        }
    }

}
