<?php

class ModifyBookmarksTable extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->remove_column("bookmarks", "loved_it");
    	$this->remove_column("bookmarks", "tired_it");
    	$this->remove_column("bookmarks", "bean_there");
    	$this->remove_column("bookmarks", "want_it");
    	$this->remove_column("bookmarks", "liked_it");
    	$this->add_column("bookmarks", "type", "string");

    }//up()

    public function down()
    {
    	$this->remove_column("bookmarks", "type");
    	$this->add_column("bookmarks", "loved_it", "boolean");
    	$this->add_column("bookmarks", "tired_it","boolean");
    	$this->add_column("bookmarks", "bean_there", "boolean");
    	$this->add_column("bookmarks", "want_it","boolean");
    	$this->add_column("bookmarks", "liked_it", "boolean");
    }//down()
}
