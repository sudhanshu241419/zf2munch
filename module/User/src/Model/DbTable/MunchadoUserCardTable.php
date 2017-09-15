<?php

namespace User\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class MunchadoUserCardTable extends AbstractDbTable {
	protected $_table_name = "munchado_debit_card";
	protected $_array_object_prototype = 'User\Model\MunchadoUserCard';
}