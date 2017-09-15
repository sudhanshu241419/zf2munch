<?php

namespace Restaurant\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class FeatureTable extends AbstractDbTable {
	protected $_table_name = "restaurant_features";
	protected $_array_object_prototype = 'Restaurant\Model\Feature';
}