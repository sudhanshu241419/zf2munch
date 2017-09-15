<?php

class AddPreColumnInUserOrder extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `user_orders` ADD `pre_order_id` INT NULL AFTER `restaurant_id` ");
    }//up()

    public function down()
    {
    	$this->remove_column("user_orders","pre_order_id");
    }//down()
}
