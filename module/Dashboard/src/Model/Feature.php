<?php

namespace Dashboard\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Where;


class Feature extends AbstractModel {
	
	public $id;
	
	protected $_db_table_name = 'Dashboard\Model\DbTable\FeatureTable';
    
  
    
	public function get_all_cuisine_by_type(){
       $select = new Select ();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array(
            '*'
        ));
        $select->where(array(
            'status' =>'1'
        ));
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $records = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->toArray();  
       return $records;
  }
 
    public function get_feature_all_data(){
      $features_data = $this->get_features_data();
      $total_data = array();
      $data = array();
      if (!empty($features_data)) {
        foreach($features_data as $f_data) {
          if ($f_data['feature_type'] == 'Social Presence' || $f_data['feature_type'] == 'Biz Info') continue;
          
          $key = strtolower(str_replace(" ", "_", $f_data['feature_type']));
          $data[$key][] = $f_data;
        }
        
        foreach($data as $k => $d) {
          $key = "total_" . strtolower(str_replace(" ", "_", $k)) . "_count";
          $total_data[$key] = count($data[$k]);
        }
      }
      $total_records = array("total_feature_data_count" => array_sum($total_data));
      return array_merge( $total_records, $total_data, $data);
    } 
    
    public function get_features_data() {
    //$conditions = array("search_status = ? and features_key != 'NULL'", 1) ; 
    
    $select = new Select ();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array(
            '*'
        ));
        $select->where(array(
            'search_status' =>'1'            
        ));
        $select->where->isNotNull('features_key');
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $records = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->toArray();  
        return $records;
		//$features = self::find_all(array("search_status" => 1));
   
	}
    
    public function get_restaurant_features_string($restaurant_id) {
        $features = array();
        $attributes = $this->get_restaurant_features($restaurant_id);
        if (!empty($attributes)) {
          foreach ($attributes as $key => $value) {
           $features[]=$value['features'] ;
        }
        $features =@implode(",", $features);
          }

        return $features ; 
    }
    
     public function get_restaurant_features($restaurant_id) {
        $select = new Select ();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array(
            'features'
        ));
         $select->join(array(
            'rf'=>'restaurant_features'
            ),'features.id = rf.feature_id',array(),$select::JOIN_INNER);
        $select->where(array(
            'rf.restaurant_id' =>$restaurant_id,
            'rf.status'=>'1'
        ));
        
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $records = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->toArray();  
        return $records; 
     }
    
}