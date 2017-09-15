<?php
namespace Dashboard\Model;

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

    protected $_db_table_name = 'Dashboard\Model\DbTable\UserDealsTable';

    protected $_primary_key = 'id';

    const USER_DATE_FORMAT = 'M d, Y H:i:s';
    
    public function createUserDeal($data){     
		//pr($data);
		$writeGateway = $this->getDbTable ()->getWriteGateway ();
		$userDeals = $this->getUserDeals(array("columns"=>array("id"),"where"=>array("user_id"=>$data['user_id'],"deal_id"=>$data['deal_id'])));
        $lastInsertId = false;
        if (empty($userDeals)) {
			$writeGateway->insert ( $data );
            $lastInsertId = $writeGateway->getAdapter ()->getDriver ()->getLastGeneratedValue ();
		} 
        //else {
//			$rowsAffected = $writeGateway->update ( $data, array (
//					'id' => $this->id 
//			) );
//		}
		// Get the last insert id and update the model accordingly
		
		
		if ($lastInsertId) {
//			if (! $this->id) {
//				$this->id = $lastInsertId;
//			}
//			return $this->toArray ();
            return true;
		}
		return false;	
    }
    
    
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
    
    public function delete($id) { 
        $writeGateway = $this->getDbTable()->getWriteGateway();
	 $writeGateway->delete ( array (
				'id' => $id 
		) );
	}
    
    public function getDealUser($dealId){
         $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $tags = new \Restaurant\Model\Tags();
        $tagsDetails = $tags->getTagDetailByName("dine-more");
        
        $joins = array();
        
        $joins [] = array(
            'name' => array(
                'u'=>'users'
            ),
            'on' => 'user_deals.user_id = u.id',
            'columns' => array(
                'first_name',
                'last_name',
                'email','id'
            ),
            'type' => 'inner'
        );       
        
        $options = array(
            'columns' => array(
                'deal_id'
            ),
        'where' => array('user_deals.deal_id'=>$dealId),
        'joins' => $joins,
            
        );
        
        $dealUser = $this->find($options)->toArray();        
        return $dealUser;
    }
    
}