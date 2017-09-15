<?php

class AddFieldInMerchantRegistration extends Ruckusing_Migration_Base
{
    public function up()
    {
    	/*$this->execute("ALTER TABLE `merchant_registration` ADD `fee_structure_id` TINYINT NULL DEFAULT '2' AFTER `restaurant_id`");*/
    	$this->execute("ALTER TABLE `menu_bookmarks` ADD `restaurant_id` INT NULL DEFAULT '0' AFTER `id`");
    }//up()

    public function down()
    {
    }//down()
}
