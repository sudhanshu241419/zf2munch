<?php

class AddColumnUserOrders extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `user_orders` ADD `order_type2` ENUM( 'p', 'b' ) NOT NULL DEFAULT 'p' COMMENT 'p-personal,b-bussiness' AFTER `order_type1`");
    }//up()

    public function down()
    {
    }//down()
}
