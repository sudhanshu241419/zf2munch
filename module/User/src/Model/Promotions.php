<?php
namespace User\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;


class Promotions extends AbstractModel
{

  public $promotionId;
  public $promotionName;
  public $promotionDesc;
  public $promotionSponsorByID;
  public $promotionPoints;
  public $promotionStartDate;
  public $promotionEndDate;
  public $promotionStatus;
  public $promotionDisplayOrder;
  public $promotionTypeId;
  public $promotionCategoryId;
  public $promotionCategoryAmount;
  public $publicminimumOrder;
  public $promotionCreatedOn;
  public $promotionUpdatedOn;

  protected $_db_table_name = 'User\Model\DbTable\PromotionsTable';

  protected $_primary_key = 'promotionId';
   
    public function insert($data){
        $writeGateway = $this->getDbTable ()->getWriteGateway ();      
        $rowsAffected = $writeGateway->insert ($data);
        if($rowsAffected){		
            return true;
        }else{
            return false;
        }
    }
    
    public function getPromotions(array $options = array()){
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $promotions = $this->find($options)->toArray();
        return $promotions;
    }
   
}