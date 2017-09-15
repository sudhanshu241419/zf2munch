<?php

namespace Dashboard\Model;

use MCommons\Model\AbstractModel;

class OrderAddons extends AbstractModel {
	public $id;
	public $user_order_detail_id;
	public $user_order_id;
	public $menu_addons_id;
	public $addons_name;
	public $addons_option;
	public $price;
	public $quantity;
	public $selection_type;
	public $menu_addons_option_id;
	public $priority;
	public $was_free;
	protected $_primary_key = 'id';
	protected $_db_table_name = 'Dashboard\Model\DbTable\OrderAddonsTable';
	public function addtoUserOrderAddons() {
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
	public function getAllOrderAddon(array $options = array()){
		$this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' );
		$orderAddonDetail = $this->find ( $options )->toArray ();
		return $orderAddonDetail;
	}
}
