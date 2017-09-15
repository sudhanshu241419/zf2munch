<?php

namespace Restaurant\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class MasterCuisineTable extends AbstractDbTable {
	protected $_table_name = "cuisines";
	protected $_array_object_prototype = 'Restaurant\Model\MasterCuisines';
}