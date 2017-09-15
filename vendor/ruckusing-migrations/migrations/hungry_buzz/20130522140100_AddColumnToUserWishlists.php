<?php

class AddColumnToUserWishlists extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("alter table user_wishlists add column menu_id int(11) after restaurant_id");
    	$this->execute("alter table user_wishlists change column status status enum('0','1','2')");
    }//up()

    public function down()
    {
    }//down()
}
