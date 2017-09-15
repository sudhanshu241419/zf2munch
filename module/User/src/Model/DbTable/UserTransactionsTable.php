<?php

namespace User\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class UserTransactionsTable extends AbstractDbTable {
    protected $_table_name = "user_transactions";
    protected $_array_object_prototype = 'User\Model\UserTransactions';
}