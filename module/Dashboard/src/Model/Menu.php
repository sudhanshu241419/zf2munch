<?php

namespace Dashboard\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;

class Menu extends AbstractModel {

    public $id;
    public $pid;
    public $restaurant_id;
    public $item_name;
    public $cuisines_name;
    public $image_name;
    public $item_desc;
    public $selection_type;
    public $created_on;
    public $status;
    // public $price;
    protected $_db_table_name = 'Dashboard\Model\DbTable\MenuTable';
    protected $_primary_key = 'id';
   
    public function restaurantMenues($restaurantId,$menuSortOrder = false) {
        $select = new Select ();
        $where = New Where();
        $path = "rest_code" . "/" . THUMB . "/" . "image_name";
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array(
            'id',            
            'item_name',            
        ));

        $select->join(array(
            'mp' => 'menu_prices'
            ), 'mp.menu_id = menus.id', array(
            
            'price' => new Expression('IF(mp.price IS NULL,0,round(mp.price,2))'),            
            ), $select::JOIN_LEFT);

       $where->equalTo('menus.restaurant_id', $restaurantId);
       $where->equalTo('menus.status', 1);
       $where->greaterThan('menus.pid', 0);
       $where->greaterThan("mp.price", 0);
       $select->where($where);
        if($menuSortOrder == 1){
             $select->order('menus.item_rank ASC');
        }
//        requested by Rahul on 25-Feb-2016
//        $r_tags = new \Home\Model\RestaurantTag(); 
//        if($r_tags->hasTags($options ['columns'] ['restaurant_id'])){ 
//            $select->order('item_rank ASC');
//        }
//        var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $allmenues = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->toArray();

        return $allmenues;
    }

    public function getTotalMenusCount($restaurant_id) {
        $select = new Select ();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array(
            'menu_count' => new Expression('COUNT(*)')
        ))->where(array(
            'restaurant_id' => $restaurant_id
        ));
        $reviewCount = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select);
        return $reviewCount;
    }

    public function getMenuDetail($menu_id) {
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $options = array(
            'columns' => array(
                'item_name',
                'restaurant_id',
                'image_name',
                'id'
            ),
            'where' => array(
                'id' => $menu_id
            )
        );

        $menuDetail = $this->find($options)->current();
        return $menuDetail;
    }
    
    public function getDealMenu($dealId){
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $tags = new \Restaurant\Model\Tags();
        $tagsDetails = $tags->getTagDetailByName("dine-more");
        
        $joins = array();
        
        $joins [] = array(
            'name' => array(
                'dm'=>'restaurant_deals_coupons'
            ),
            'on' => 'dm.menu_id = menus.id',
            'columns' => array(
                'menu_id',              
                
            ),
            'type' => 'inner'
        );       
        
        $options = array(
            'columns' => array(
                'item_name'
            ),
        'where' => array('dm.id'=>$dealId),
        'joins' => $joins,
            
        );
        
        $dealUser = $this->find($options)->toArray();        
        return $dealUser;
    }
}

//end of class
