<?php

namespace Dashboard\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class MerchantTable extends AbstractDbTable {
	protected $_table_name = "merchant_registration";
	protected $_array_object_prototype = 'Dashboard\Model\MerchantRegistration';
}