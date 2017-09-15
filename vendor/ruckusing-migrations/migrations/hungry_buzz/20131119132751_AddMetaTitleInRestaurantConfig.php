<?php

class AddMetaTitleInRestaurantConfig extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `restaurant_config` ADD `meta_title` VARCHAR( 255 ) NOT NULL AFTER `gadomain` ,
			ADD `meta_description` TEXT NOT NULL AFTER `meta_title` ,
			ADD `meta_keyword` TEXT NOT NULL AFTER `meta_description` ,
			ADD `created_date` DATETIME NULL AFTER `meta_keyword`;");
    }//up()

    public function down()
    {
    }//down()
}
