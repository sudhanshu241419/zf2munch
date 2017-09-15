<?php

namespace Dashboard\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class UserPointTable extends AbstractDbTable {
    protected $_table_name = "user_points";
    protected $_array_object_prototype = 'Dashboard\Model\UserPoint';
}