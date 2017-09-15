<?php

namespace Dashboard\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class CmsSolrindexingTable extends AbstractDbTable {
	protected $_table_name = "cms_solr_indexing";
	protected $_array_object_prototype = 'Dashboard\Model\CmsSolrindexing';
}