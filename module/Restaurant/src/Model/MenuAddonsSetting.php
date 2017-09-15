<?php

namespace Restaurant\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;

class MenuAddonsSetting extends AbstractModel {
	public $id;
	public $menu_id;
	public $addon_id;
	public $item_limit;
	public $quantity_no;
	public $enable_pricing_beyond;
	public $meal_part;
	protected $_db_table_name = 'Restaurant\Model\DbTable\MenuAddonSettingTable';
	protected $_primary_key = 'id';
	public function gitItemLimit(array $options = array()) {
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$select->columns ( array (
				'item_limit' 
		) );
		
		$select->where ( array (
				'menu_id' => $options ['columns'] ['menu_id'],
				'addon_id' => $options ['columns'] ['addon_id'] 
		)
		 );
		$itemLimit = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select );
		return $itemLimit;
	}
	public function getEnablePriceBeyound(array $options= array()){
		$select = new Select();
		$select->from($this->getDbTable()->getTableName());
		$select->columns(array('enable_pricing_beyond' => new Expression('MAX(enable_pricing_beyond)')));
		$select->where(array('addon_id'=>$options['where']['addon_id']));
        $select->where(array('menu_id'=>$options['where']['menu_id']));
		//var_dump($select->getSqlString($this->getPlatform('READ')));
        $enbDetail = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->toArray();
		
        return $enbDetail;
	}
    
    public function reorderAddonSetting($addonId){
        $select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$select->columns ( array (
				'item_limit', 'enable_pricing_beyond'
		) );
		
		$select->where ( array (				
				'addon_id' => $addonId 
		)
		 );
		$reorderAddonSetting = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select )->current();
		return $reorderAddonSetting;
    }
}//end of class