<?php
namespace User\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;

class UserDeals extends AbstractModel
{

    public $id;

    public $user_id;

    public $deal_id;

    public $deal_status;

    public $availed;

    public $date;

    protected $_db_table_name = 'User\Model\DbTable\UserDealsTable';

    protected $_primary_key = 'id';

    const USER_DATE_FORMAT = 'M d, Y H:i:s';

    public function updateUserDeals()
    {
        $data['availed'] =1;        
        $writeGateway = $this->getDbTable()->getWriteGateway();
        $rowsAffected = $writeGateway->update($data, array(
           'user_id' => $this->user_id,
           'deal_id' => $this->deal_id,
        ));
                
        if ($rowsAffected >= 1) {
           return $this->toArray();
        }
        return false;
     
    }

    /**
     *
     * @param array $options            
     * @return array
     */
    public function getUserDeals(array $options = array())
    {
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        return $this->find($options)->toArray();
    }  
    
     public function readUserDeals($uid=0)
    {
        $data['read'] =1;        
        $writeGateway = $this->getDbTable()->getWriteGateway();
        $rowsAffected = $writeGateway->update($data, array(
           'user_id' => $uid
        ));
                
        if ($rowsAffected >= 1) {
           return true;
        }
        return false;
     
    }
    
}