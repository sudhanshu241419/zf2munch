<?php

namespace User\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class AppReleasedCampaignTable extends AbstractDbTable {
	protected $_table_name = "app_released_campaign";
	protected $_array_object_prototype = 'User\Model\AppReleasedCampaign';
}