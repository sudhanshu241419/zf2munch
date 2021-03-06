<?php
namespace User\Model;

use MCommons\Model\AbstractModel;
use Authenticationchanel\Model\Authenticationchanel;
use MCommons\StaticOptions;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;

class UserAccount extends AbstractModel
{

    public $user_id;
    
    public $user_name;

    public $first_name;

    public $last_name;
    
    public $display_pic_url;

    public $user_source;

    public $display_pic_url_normal;

    public $display_pic_url_large;

    public $session_token;

    public $access_token;

    protected $_db_table_name = 'User\Model\DbTable\UserAccountTable';

    protected $_primary_key = 'id';
    
    
    public function getUserDetail(array $options = array())
    {       
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $response = $this->find($options)->current();
        return $response;
    }

    public function getUser($options)
    {
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $userDetail = $this->find($options)->toArray();
        return current($userDetail);
    }

    public function userAccountRegistration()
    {
        $data = $this->toArray();
        $writeGateway = $this->getDbTable()->getWriteGateway();
        $userDetails = $this->CheckUserAccount($this->user_id,$this->user_source);
        if (($userDetails[0]['accountExist']==0)) {
            $rowsAffected = $writeGateway->insert($data);
            $lastInsertId = $writeGateway->getAdapter()
                ->getDriver()
                ->getLastGeneratedValue();
        } else {
            $rowsAffected = $writeGateway->update($data, array(
               'user_id' => $this->user_id,
               'user_source'=> $this->user_source
            ));
            $lastInsertId = $this->user_id;
        }
        if ($rowsAffected >= 1) {
            $this->id = $lastInsertId;
            return $this->toArray();
        }
        return false;
    }
    
    public function CheckUserAccount($user_id,$user_source=false)
    {
        $select = new Select();
        $select->from($this->getDbTable()
            ->getTableName());
        $select->columns(array(
            'accountExist'=>new Expression('count(*)')
        ));
        
        $where = new Where();
        $where->equalTo('user_id', $user_id);
        if($user_source){
            $where->equalTo('user_source', $user_source);
        }else{
            $where->in('user_source', array("iOS","ws"));
        }
        
        $select->where($where);
        //pr($select->getSqlString($this->getPlatform('READ')),true);
        $userAcStatus = $this->getDbTable()
            ->setArrayObjectPrototype('ArrayObject')
            ->getReadGateway()
            ->selectWith($select);
       
        return $userAcStatus->toArray();
    }

    public function getName($userId)
    {
        $record = $this->getUserDetail(array(
            'columns' => array(
                'first_name',
                'last_name'
            ),
            'where' => array(
                'id' => $userId
            )
        ));
        
        if (! empty($record)) {
            $record->getArrayCopy();
            return $record['first_name'] . ' ' . $record['last_name'];
        }
        return "";
    }
    
    public function update($data)
    {      
        $writeGateway = $this->getDbTable()->getWriteGateway();
        $rowsAffected = $writeGateway->update($data, array(
            'user_id' => $this->user_id,
            'user_source' => $this->user_source
        ));
        if($rowsAffected){
            return true;
        }else{
            return false;
        }
    }
    
}    