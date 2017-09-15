<?php

namespace Restaurant\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class MenuCuisineTable extends AbstractDbTable {
	protected $_table_name = "Menu_Cuisines";
	protected $_array_object_prototype = 'Restaurant\Model\MenuCuisine';
}