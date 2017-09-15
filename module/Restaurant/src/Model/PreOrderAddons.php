<?php

namespace Restaurant\Model;

use MCommons\Model\AbstractModel;

class PreOrderAddons extends AbstractModel {
	public $id;
	public $pre_order_item_id;
	public $pre_order_id;
	public $menu_addons_id;
	public $addons_name;
	public $addons_option;
	public $price;
	public $quantity;
	public $selection_type;
	protected $_primary_key = 'id';
	const ORDER_PENDING = '0';
	const ORDER_CHECKOUT = '1';
	protected $_db_table_name = 'Restaurant\Model\DbTable\PreOrderAddonsTable';
	public function addtoPreOrderAddons() {
		$data = $this->toArray ();
		
		$writeGateway = $this->getDbTable ()->getWriteGateway ();
		
		$rowsAffected = $writeGateway->insert ( $data );
		
		// Get the last insert id and update the model accordingly
		$lastInsertId = $writeGateway->getAdapter ()->getDriver ()->getLastGeneratedValue ();
		
		if ($rowsAffected >= 1) {
			return $lastInsertId;
		}
		return false;
	}
	public function getUserPreOrderAddons(array $options = array())
	{
		$this->getDbTable()->setArrayObjectPrototype('ArrayObject');
		return $this->find($options)->toArray();
	}
}
