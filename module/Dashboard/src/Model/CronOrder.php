<?php
namespace Dashboard\Model;
use MCommons\Model\AbstractModel;
class CronOrder extends AbstractModel{
    public $id;

    public $order_id;

    public $delivery_time;

    public $arrived_time;

    public $archive_time;

    public $time_zone;

    public $status;

    protected $_db_table_name = 'Dashboard\Model\DbTable\CronOrderTable';
   
    public function save()
    {
        $data = $this->toArray();        
        $writeGateway = $this->getDbTable()->getWriteGateway();        
        if (!$this->id) {
            $rowsAffected = $writeGateway->insert($data);
        } else {
            $rowsAffected = $writeGateway->update($data, array(
                'id' => $this->id
            ));
                }
        // Get the last insert id and update the model accordingly
        $lastInsertId = $writeGateway->getAdapter()
            ->getDriver()
            ->getLastGeneratedValue();
        
        if ($rowsAffected >= 1) {
            $this->id = $lastInsertId;
            return $this->toArray();
        }
        return false;
    }
    public function getCronOrder(array $options = array())
    {
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        return $this->find($options)->current();
    }
}


