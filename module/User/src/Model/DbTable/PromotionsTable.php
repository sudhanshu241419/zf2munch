<?php

namespace User\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class PromotionsTable extends AbstractDbTable {
	protected $_table_name = "promotions";
	protected $_array_object_prototype = 'User\Model\promotions';
}