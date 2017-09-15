<?php

namespace Restaurant\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class SolrIndexingTable extends AbstractDbTable {
	protected $_table_name = "cms_solr_indexing";
	protected $_array_object_prototype = 'Restaurant\Model\SolrIndexing';
}