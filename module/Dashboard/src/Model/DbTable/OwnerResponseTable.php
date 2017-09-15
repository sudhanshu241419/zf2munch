<?php

namespace Dashboard\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class OwnerResponseTable extends AbstractDbTable {
	protected $_table_name = "owner_response";
	protected $_array_object_prototype = 'Dashboard\Model\OwnerResponse';
}