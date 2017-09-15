<?php

class AddColumnInUsersTable extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->add_column("users", "display_pic_url_normal", "string");
    	$this->add_column("users", "display_pic_url_large", "string");
    }//up()

    public function down()
    {
    	$this->remove_column("users", "display_pic_url_normal");
    	$this->remove_column("users", "display_pic_url_large");
    }//down()
}
