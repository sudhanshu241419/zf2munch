<?php

class AddIsDeletedInUserOrders extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `user_orders` ADD `is_deleted` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `host_name`");
    }//up()

    public function down()
    {
    }//down()
}
