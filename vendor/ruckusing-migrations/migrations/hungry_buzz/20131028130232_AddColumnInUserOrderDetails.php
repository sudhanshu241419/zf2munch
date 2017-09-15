<?php

class AddColumnInUserOrderDetails extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `user_order_details` ADD `item_price_id` INT( 11 ) NOT NULL DEFAULT '0' AFTER `item_id`");
    }//up()

    public function down()
    {
    }//down()
}
