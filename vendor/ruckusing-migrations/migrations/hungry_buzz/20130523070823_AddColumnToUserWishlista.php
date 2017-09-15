<?php

class AddColumnToUserWishlista extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("alter table user_wishlists change column menu_id review_id int(11)");
    	$this->execute("alter table user_wishlists add column type enum('WISHLIST','USEFUL')");
    }//up()

    public function down()
    {
    }//down()
}
