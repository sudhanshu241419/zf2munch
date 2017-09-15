<?php

namespace Servers\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class ServerRewardsTable extends AbstractDbTable {
	protected $_table_name = "server_rewards";
	protected $_array_object_prototype = 'Servers\Model\ServerRewards';
}