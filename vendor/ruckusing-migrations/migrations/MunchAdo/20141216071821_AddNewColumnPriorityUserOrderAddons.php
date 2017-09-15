<?php

class AddNewColumnPriorityUserOrderAddons extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `user_order_addons` ADD `priority` INT( 11 ) NOT NULL AFTER `selection_type` ,
ADD `worse_free` ENUM( '1', '0' ) NOT NULL DEFAULT '0' AFTER `priority`");
    }//up()

    public function down()
    {
    }//down()
}
