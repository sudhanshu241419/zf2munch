<?php

namespace Restaurant\Model;

use MCommons\Model\AbstractModel;

class Addons extends AbstractModel {
	public $id;
	public $addon_name;
	public $status;
	protected $_db_table_name = 'Restaurant\Model\DbTable\AddonTable';
	protected $_primary_key = 'id';
	public function getAddon(array $options = array()) {
		return $this->find ( $options );
	}
}

