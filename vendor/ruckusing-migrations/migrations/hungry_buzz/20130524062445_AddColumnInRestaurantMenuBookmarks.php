<?php

class AddColumnInRestaurantMenuBookmarks extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("alter table restaurant_bookmarks add column restaurant_name varchar(255) after restaurant_id");
    	$this->execute("alter table restaurant_bookmarks change column date_time created_on timestamp");
    	$this->execute("alter table menu_bookmarks change column date_time created_on timestamp");
    	$this->execute("alter table menu_bookmarks add column menu_name varchar(255) after menu_id");
    }//up()

    public function down()
    {
    }//down()
}
