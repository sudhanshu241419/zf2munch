<?php

namespace Restaurant\Model;

use MCommons\Model\AbstractModel;
use MCommons\StaticOptions;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Where;

class SolrIndexing extends AbstractModel {

    public $id;
    public $restaurant_id;
    public $rest_code;
    public $updated_on;
    public $is_indexed;
    public $indexed_on;
    protected $_db_table_name = 'Restaurant\Model\DbTable\SolrIndexingTable';
    protected $_primary_key = 'id';

    public function findRestaurant($options) {
        $restaurant = $this->find($options);
        return $restaurant;
    }

    public function updateSolrIndexing($data) {       
        $writeGateway = $this->getDbTable()->getWriteGateway();
        if ($this->id) {
            $rowsAffected = $writeGateway->update ( $data, array (
				'id' => $this->id 
		) );
           
            return true;
        } else {
            $rowsAffected = $writeGateway->insert($data);
            return true;
        }
    }

}
