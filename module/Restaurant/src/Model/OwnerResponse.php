<?php
namespace Restaurant\Model;

use MCommons\Model\AbstractModel;

class OwnerResponse extends AbstractModel
{

    public $id;

    public $review_id;

    public $response;

    public $response_date;

    protected $_db_table_name = 'Restaurant\Model\DbTable\OwnerResponseTable';

    public function addResponse($data = array())
    {
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
}