<?php
namespace User\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class PointSourceDetailsTable extends AbstractDbTable {
	protected $_table_name = "point_source_detail";
	protected $_array_object_prototype = 'User\Model\PointSourceDetails';
}