<?php

class ChangeColumnNameInbookmarkstable extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->add_column("bookmarks", "entity_name", "string");
    	$this->rename_column("bookmarks", "date_time", "created_at");
    }//up()

    public function down()
    {
    	$this->remove_column("bookmarks", "entity_name");
    	$this->rename_column("bookmarks", "created_at", "date_time");
    }//down()
}
