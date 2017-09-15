<?php

namespace Restaurant\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class GalleryTable extends AbstractDbTable {
	protected $_table_name = "restaurant_images";
	protected $_array_object_prototype = 'Restaurant\Model\Gallery';
}