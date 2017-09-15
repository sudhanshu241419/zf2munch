<?php
namespace User\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;


class SmsOffer extends AbstractModel
{

    public $id;
       
    public $restaurant_id;

    public $match_case;
    
    public $message;
    
    public $created_at;
    
    public $update_at;
    
    public $status;

    protected $_db_table_name = 'User\Model\DbTable\smsOfferTable';

    protected $_primary_key = 'id';
   
    public function getMessage($matchCase,$code){
        $restaurantId = substr($code, 2, strlen($code) - 1);
        $ma = strtolower(substr($code,0,2));
        if($ma != "ma"){
            return false;
        }
        $restaurant = new \Restaurant\Model\Restaurant();
        $isRestaurant = $restaurant->isRestaurantExists($restaurantId);
        if($isRestaurant == 1){
            $select = new Select ();
            $select->from ( $this->getDbTable ()->getTableName () );
            $select->columns ( array (
                    'id',
                    'restaurant_id',
                    'message',				
            ) );
            $where = new Where ();


            $where->equalTo ( 'match_case', strtolower($matchCase) );
            $where->equalTo('restaurant_id', $restaurantId);
            $where->equalTo("status", 1);

            $select->where ( $where );

            //var_dump($select->getSqlString($this->getPlatform('READ')));
            $messageDetails = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select )->toArray();
            
            if(!empty($messageDetails)){
                return $messageDetails[0]['message'];
            }
        }
        return false;
        
	}
   
}