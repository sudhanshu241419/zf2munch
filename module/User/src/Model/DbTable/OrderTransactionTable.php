<?php

namespace User\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class OrderTransactionTable extends AbstractDbTable {
    protected $_table_name = "order_transaction";
    protected $_array_object_prototype = 'User\Model\OrderTransaction';
}