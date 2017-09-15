<?php

class AddColumnRestaurantOrderInvitation extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `user_order_invitation` ADD `restaurant_id` INT( 11 ) NOT NULL AFTER `from_id`");
    }//up()

    public function down()
    {
    	$this->execute("ALTER TABLE `user_order_invitation` DROP `restaurant_id` INT( 11 ) NOT NULL AFTER `from_id`");
    }//down()
}
