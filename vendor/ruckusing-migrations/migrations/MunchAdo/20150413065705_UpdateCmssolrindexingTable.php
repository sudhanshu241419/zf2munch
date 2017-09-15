<?php

class UpdateCmssolrindexingTable extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE cms_solr_indexing ADD COLUMN closed TINYINT NOT NULL DEFAULT 0;");
        $this->execute("ALTER TABLE cms_solr_indexing ADD INDEX i_closed (closed);");
    }//up()

    public function down()
    {
    }//down()
}
