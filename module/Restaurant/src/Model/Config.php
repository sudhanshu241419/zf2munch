<?php

namespace Restaurant\Model;

use MCommons\Model\AbstractModel;

class Config extends AbstractModel {
	public $id;
	public $restaurant_id;
	public $hostname;
	public $base_url;
	public $assets_url;
	public $web_url;
	public $other_settings;
	public $default_protocol;
	protected $_db_table_name = 'Restaurant\Model\DbTable\ConfigTable';
	protected $_primary_key = 'id';
	public function getConfig(array $options = array()) {
		$this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' );
		$config = $this->find ( $options )->current ();
		if ($config) {
			$config = $config->getArrayCopy ();
			$otherSettings = @unserialize ( $config ['other_settings'] );
			$config ['other_settings'] = $otherSettings ? $otherSettings : array ();
		} else {
			$config = array ();
		}
		return $config;
	}
}