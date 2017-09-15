<?php

class AddAddonOption extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute('ALTER TABLE `user_order_addons` ADD `menu_addons_option_id` INT NULL DEFAULT NULL AFTER `menu_addons_id` ');
    }//up()

    public function down()
    {
    }//down()
}
