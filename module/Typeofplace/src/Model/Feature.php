<?php

namespace Typeofplace\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class Feature extends AbstractModel {
	public $id;
	public $features;
	public $feature_type;
	public $search_status;
	public $status;
	public $features_key;
	protected $_db_table_name = 'Typeofplace\Model\DbTable\FeatureTable';
	protected $_primary_key = 'id';
	public function getFeature(array $options = array()) {
               // $featureId = array(26,30,35,38,39,40,46,60,65,96);
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$select->columns ( array () );
		
		$select->columns ( array (
				'id',
				'features',
				'feature_type',
				'features_key' 
		) );
		$where = new Where();
        $where->equalTo('search_status', 1);
        $select->where ( $where );
        //pr($select->getSqlString($this->getPlatform('READ')),true);	
		$featureData = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select );
		
		return $featureData;
	}
        
        public function getFeatureNew(array $options = array()) 
        {
            $select = new Select ();
            $select->from ( $this->getDbTable ()->getTableName () );
            $select->columns ( array () );
		
            $select->columns ( array ( 'id','features','feature_type', 'features_key' ) );
            //pr($select->getSqlString($this->getPlatform('READ')),true);	
            $featureData = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select );
		
            return $featureData;
	}
}