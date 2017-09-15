<?php

namespace Cuisine\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class CuisineTable extends AbstractDbTable {
	protected $_table_name = "cuisines";
	protected $_array_object_prototype = 'Cuisine\Model\Cuisine';
}