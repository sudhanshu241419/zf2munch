<?php

class AddColumnInBookmarks extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("alter table bookmarks add column restaurant_id int(11) NOT NULL AFTER user_id");
    }//up()

    public function down()
    {
    }//down()
}
