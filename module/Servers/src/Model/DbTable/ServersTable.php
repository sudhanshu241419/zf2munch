<?php

namespace Servers\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class ServersTable extends AbstractDbTable {
	protected $_table_name = "servers";
	protected $_array_object_prototype = 'Servers\Model\Servers';
}