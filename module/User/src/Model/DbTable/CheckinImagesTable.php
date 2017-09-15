<?php

namespace User\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class CheckinImagesTable extends AbstractDbTable {
	protected $_table_name = "checkin_images";
	protected $_array_object_prototype = 'User\Model\CheckinImages';
}