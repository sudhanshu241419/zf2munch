<?php
namespace User\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;


class MunchAdoDebitCard extends AbstractModel
{

  public $id;
  public $user_id;
  public $card_number;
  public $card_type;
  public $name_on_card;
  public $expired_on;
  public $created_on;
  public $updated_at;
  public $status;

  protected $_db_table_name = 'User\Model\DbTable\MunchadoDebitCardTable';

  protected $_primary_key = 'id';
   
    public function countUserMunchAdoCard($userId,$currentDate){
       $select = new Select();
       $select->from($this->getDbTable()->getTableName());
       $select->columns(array(
             'card_number',
             'expired_on'
        ));
        $where = new Where();
        $where->equalTo('user_id', $userId);
        $where->equalTo('status', 1);
        $select->where($where);
        //pr($select->getSqlString($this->getPlatform('READ')),true);
        $userMunchAdoCard = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->toArray();
        if($userMunchAdoCard){
           $expireMonthYear = explode("/", $userMunchAdoCard[0]['expired_on']);
           
           $currentTimeStamp = strtotime($currentDate);
           $currentMonth = date("m",$currentTimeStamp);
           $currentYear = date('y',$currentTimeStamp);
           if($expireMonthYear[0]>=$currentMonth && $expireMonthYear[1]>=$currentYear){
                return $userMunchAdoCard[0]['card_number']; 
           }else{
                return false;
           }
        }else{
            return false;
        }
    }
   
}