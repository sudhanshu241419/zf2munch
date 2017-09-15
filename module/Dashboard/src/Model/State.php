<?php
namespace Dashboard\Model;

use MCommons\Model\AbstractModel;
class State extends AbstractModel {

    public $id;
    public $country_id;
    public $state;
    public $state_code;
    public $zone;
    public $status;    
    protected $_db_table_name = 'Dashboard\Model\DbTable\StateTable';
    protected $_primary_key = 'id';

    public function getState(array $options = array()) {
        return $this->find($options);
    }    
}
