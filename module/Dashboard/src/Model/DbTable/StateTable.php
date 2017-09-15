<?php

namespace Dashboard\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class StateTable extends AbstractDbTable {
	protected $_table_name = "states";
	protected $_array_object_prototype = 'Dashboard\Model\State';
}

