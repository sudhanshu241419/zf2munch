<?php

class CreateBookmarkTable extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$bookmarks = $this->create_table("bookmarks");

    		$bookmarks->column("entity_id", "integer");	
    		$bookmarks->column("entity_type", "string");
    		$bookmarks->column("user_id", "integer");
    		$bookmarks->column("date_time", "datetime");
    		$bookmarks->column("loved_it", "boolean");
    		$bookmarks->column("tired_it", "boolean");
    		$bookmarks->column("bean_there", "string");
    		$bookmarks->column("want_it", "boolean");
    		$bookmarks->column("liked_it", "boolean");
    		$bookmarks->finish();
    }//up()

    public function down()
    {
      $this->drop_table("bookmarks");
    }//down()
}
