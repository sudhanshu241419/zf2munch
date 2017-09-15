<?php

class ChangeColumnInUserOrderAddon extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `user_order_addons` CHANGE `user_order_details_id` `user_order_detail_id` INT( 11 ) NOT NULL DEFAULT '0' ");
    }//up()

    public function down()
    {
    }//down()
}
