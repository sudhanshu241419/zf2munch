<?php

namespace Dashboard\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class FeatureTable extends AbstractDbTable {
	protected $_table_name = "features";
	protected $_array_object_prototype = 'Dashboard\Model\Feature';
}