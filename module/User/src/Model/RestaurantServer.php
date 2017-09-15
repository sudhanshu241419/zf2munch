<?php

namespace User\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;
class RestaurantServer extends AbstractModel {

    public $id;
    public $user_id;
    public $restaurant_id;
    public $code;
    public $date;
    public $status = 0;
    protected $_db_table_name = 'User\Model\DbTable\RestaurantServerTable';
    public function registerRestaurantServer() {
        $data = $this->toArray();
        $writeGateway = $this->getDbTable()->getWriteGateway();
        
        if (!$this->id) {
            $rowsAffected = $writeGateway->insert($data);
            // Get the last insert id and update the model accordingly
            $lastInsertId = $writeGateway->getAdapter()->getDriver()->getLastGeneratedValue();
        } else {
            $rowsAffected = $writeGateway->update($data, array(
                'id' => $this->id
            ));
            $lastInsertId = $this->id;
        }

        if ($rowsAffected >= 1) {
            return $this->id = $lastInsertId;            
        }
        return false;
    }
    
    public function checkExistCodeWithUser($userId,$restaurantId){
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');        
        $options = array(
            'columns' => array(
                'id'=>new \Zend\Db\Sql\Expression('count(id)')                
            ),
            'where' => array('user_id' => $userId,'restaurant_id'=>$restaurantId),
        );
        return $this->find($options)->toArray();        
    }
    
    public function isUserRegister(){
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');        
        $options = array(
            'columns' => array(
                'id'=>new \Zend\Db\Sql\Expression('count(id)')                
            ),
            'where' => array('user_id' => $this->user_id),
        );
        return $this->find($options)->toArray();  
    }
    
    public function isUserRegisterWithRestaurant(){
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject'); 
        $joins = array();
        $joins[] = array(
            'name' => array(
                'rt'=>'restaurant_tags'
            ),
            'on' => 'restaurant_servers.restaurant_id = rt.restaurant_id',
            'columns' => array('tag_id','rest_short_url'),
            'type' => 'inner'
        );
        $options = array(
            'columns' => array(
                'id'=>new \Zend\Db\Sql\Expression('count(restaurant_servers.id)')                
            ),
            'where' => array('user_id' => $this->user_id,'restaurant_servers.restaurant_id'=>$this->restaurant_id,'rt.status'=>1),
            'joins'=>$joins
        );
        return $this->find($options)->toArray();  
    }
    public function findExistingUser() {
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');   
		$options = array(
            'columns' => array(
                'id','user_id','restaurant_id','date','code'                
            ),
            'where' => array('user_id' => $this->user_id,'restaurant_id'=>$this->restaurant_id),
        );
        return $this->find($options)->toArray();
	}
    
     public function userDineAndMoreRestaurant($userId){
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $tags = new \Restaurant\Model\Tags();
        $tagsDetails = $tags->getTagDetailByName("dine-more");
        
        $joins = array();
        
        $joins [] = array(
            'name' => array(
                'r'=>'restaurants'
            ),
            'on' => 'restaurant_servers.restaurant_id = r.id',
            'columns' => array(
                'restaurant_id' => 'id',
                'restaurant_name',
                'restaurant_image_name',
                'rest_code',
            ),
            'type' => 'inner'
        );       
        $joins[] = array(
            'name' => array(
                'rt'=>'restaurant_tags'
            ),
            'on' => 'restaurant_servers.restaurant_id = rt.restaurant_id',
            'columns' => array('tag_id','rest_short_url'),
            'type' => 'inner'
        );   
        
        $options = array(
            'columns' => array(
                'code'
            ),
            'where' => array('user_id' => $userId,'r.closed'=>0,'r.inactive'=>0,'rt.status'=>1,'rt.tag_id'=>$tagsDetails[0]['tags_id']), //array('users.city_id' => 18848),  
            'joins' => $joins,
            
        );
        $userDineAndMoreRestaurant = $this->find($options)->toArray();        
        return $userDineAndMoreRestaurant;
     }
     
     public function getInactiveUserFromRestaurantServer(){
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');  
        $options = array(
            'columns' => array(
                'id','user_id','restaurant_id','date','code','status'                
            ),
            'where' => array('status' => 0),
        );
        return $this->find($options)->toArray();
     }
     
     public function update($data) {
		$writeGateway = $this->getDbTable ()->getWriteGateway ();
        $rowAffected = $writeGateway->update ( $data, array (
				'id' => $this->id 
		) );
                
		return $rowAffected;
	}
    
    public function featuresUserDineAndMoreRestaurant($userId,$limit = FALSE, $order = FALSE){
         
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $tags = new \Restaurant\Model\Tags();
        $tagsDetails = $tags->getTagDetailByName("dine-more");
        
        $joins = array();
        
        $joins [] = array(
            'name' => array(
                'r'=>'restaurants'
            ),
            'on' => 'restaurant_servers.restaurant_id = r.id',
            'columns' => array(
                'id',
                'restaurant_name',
                'res_code' => 'rest_code',
                'price',              
                'allowed_zip',
                'restaurant_image_name',
                'minimum_delivery',
                'description',
                'city_id',
                'address',
                'zipcode',                
                'has_delivery' => 'delivery',
                'has_takeout' => 'takeout',
                'has_dining' => 'dining',
                'has_menu' => 'menu_available',
                'has_reservation' => 'reservations',
                'price' => 'price',
                'delivery_area',
                'minimum_delivery',
                'delivery_charge',
                'latitude',
                'longitude',
                'accept_cc',
                'menu_without_price',
                'accept_cc_phone',
                'phone_no',
                'delivery_desc',
                'allowed_zip',
                'restaurant_image_name',
                'order_pass_through'
            ),
            'type' => 'inner'
        );       
        $joins[] = array(
            'name' => array(
                'rt'=>'restaurant_tags'
            ),
            'on' => 'restaurant_servers.restaurant_id = rt.restaurant_id',
            'columns' => array('tag_id','rest_short_url'),
            'type' => 'inner'
        );   
        
        $options = array(
            'columns' => array(
                'code'
            ),
            'where' => array('user_id' => $userId,'r.closed'=>0,'r.inactive'=>0,'rt.status'=>1,'rt.tag_id'=>$tagsDetails[0]['tags_id']), //array('users.city_id' => 18848),  
            'joins' => $joins,
            
        );
        
        $options['order'] = new \Zend\Db\Sql\Predicate\Expression ( 'RAND()' );
       
        $userDineAndMoreRestaurant = $this->find($options)->toArray();        
        return $userDineAndMoreRestaurant;
     }
      public function isUserRegisterWithAnyRestaurant(){
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');        
        $options = array(
            'columns' => array(
                'id'=>new \Zend\Db\Sql\Expression('count(id)')                
            ),
            'where' => array('user_id' => $this->user_id),
        );
        return $this->find($options)->toArray();  
    }
    
    public function getUserDineAndMoreUntagRestaurant(){
        $tags = new \Restaurant\Model\Tags();
        $tagsDetails = $tags->getTagDetailByName("dine-more");
        $select = new Select();        
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array('user_id','restaurant_id','code'));
        
        $select->join(array(
            'rs' => 'restaurants'
        ), 'rs.id =  restaurant_servers.restaurant_id', array(
            'restaurant_name'
        ), $select::JOIN_INNER);
        
        $select->join(array(
            'rt' => 'restaurant_tags'
        ), 'restaurant_servers.restaurant_id = rt.restaurant_id', array(
            'tag_id','status'
        ), $select::JOIN_INNER);
        
        $select->join(array(
            'u' => 'users'
        ), 'restaurant_servers.user_id = u.id', array(
            'email'
        ), $select::JOIN_INNER);
        
        $where = new Where();
        
       
        $where->NEST->equalTo('rt.status', 0)->AND->equalTo('rt.tag_id', $tagsDetails[0]['tags_id'])->AND->equalTo('u.city_id', 18848)->UNNEST->OR->equalTo('rs.closed', 1)->OR->equalTo('rs.inactive',1);
        $select->group('restaurant_servers.user_id');
        $select->where($where);       
        //var_dump($select->getSqlString($this->getPlatform('READ'))); die();        
        return $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->toArray();
        
     }
     
}