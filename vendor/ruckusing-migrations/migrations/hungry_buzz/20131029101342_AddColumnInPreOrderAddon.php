<?php

class AddColumnInPreOrderAddon extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `pre_order_addons` ADD `quantity` INT NOT NULL DEFAULT '0'");
    	$this->execute("ALTER TABLE `pre_order_addons` ADD `selection_type` tinyint(4) NOT NULL DEFAULT '0'");
    }//up()

    public function down()
    {
    }//down()
}
