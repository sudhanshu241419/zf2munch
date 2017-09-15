<?php
namespace User\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Where;

class OrderTransaction extends AbstractModel
{
    public $user_id;
    protected $_db_table_name = 'User\Model\DbTable\OrderTransactionTable';
    
    public function getUserOrderTransection($user_id){
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $options = array(
            'where' => array('user_id' => $user_id)
        );
        $userEarning = $this->find($options)->current();
        return $userEarning;
    }
    public function insertRecord($uId) {
        $data['user_id'] =$uId;
        return $this->getDbTable()->getWriteGateway()->insert($data);
    }
    public function deleteRecord($uId){
       $writeGateway = $this->getDbTable ()->getWriteGateway ();
	   $rowsAffected = $writeGateway->delete ( array ('user_id' => $uId));
       return $rowsAffected;
	}
}