<?php

namespace Restaurant\Model;

use MCommons\Model\AbstractModel;

class MenuPrices extends AbstractModel {
	public $id;
	public $menu_id;
	public $price_type;
	public $price;
	public $price_desc;
	protected $_db_table_name = 'Restaurant\Model\DbTable\MenuPriceTable';
	protected $_primary_key = 'id';
}//end of class