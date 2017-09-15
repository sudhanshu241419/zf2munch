<?php

namespace Dashboard\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Where;


class Story extends AbstractModel {
	
	public $id;
	
	protected $_db_table_name = 'Dashboard\Model\DbTable\StoryTable';
    
  
    
	public function get_story($restaurant_id){
       $select = new Select ();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array(
            '*'
        ));
        $select->where(array(
            'restaurant_id' =>$restaurant_id
        ));
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $records = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->toArray();  
       return $records;
  }
  
}