<?php

class CreateMenuAddonSettings extends Ruckusing_Migration_Base
{
    public function up()
    {
    	//$this->execute("ALTER TABLE `menues` ADD `online_order_allowed` TINYINT( 1 ) NOT NULL DEFAULT '1' AFTER `status`");

    	//$this->execute("ALTER TABLE `menues_temp` ADD `online_order_allowed` TINYINT( 1 ) NOT NULL DEFAULT '1' AFTER `tag_name`");

    	//$this->execute("ALTER TABLE `menues_temp_error` ADD `online_order_allowed` TINYINT( 1 ) NOT NULL DEFAULT '1' AFTER `tag_name");

    	/*$this->execute("ALTER TABLE `addons_temp` ADD `price1_description` VARCHAR( 50 ) NULL AFTER `description` ,
						ADD `price2_description` VARCHAR( 50 ) NULL AFTER `price` ,
						ADD `price2` VARCHAR( 10 ) NULL AFTER `price2_description`,
						ADD `price3_description` VARCHAR( 50 ) NULL AFTER `price2` ,
						ADD `price3` VARCHAR( 10 ) NULL AFTER `price3_description`,
						ADD `price4_description` VARCHAR( 50 ) NULL AFTER `price3` ,
						ADD `price4` VARCHAR( 10 ) NULL AFTER `price4_description`,
						ADD `price5_description` VARCHAR( 50 ) NULL AFTER `price4` ,
						ADD `price5` VARCHAR( 10 ) NULL AFTER `price5_description`,
						ADD `price6_description` VARCHAR( 50 ) NULL AFTER `price5` ,
						ADD `price6` VARCHAR( 10 ) NULL AFTER `price6_description`,
						ADD `price7_description` VARCHAR( 50 ) NULL AFTER `price6` ,
						ADD `price7` VARCHAR( 10 ) NULL AFTER `price7_description`,
						ADD `price8_description` VARCHAR( 50 ) NULL AFTER `price7` ,
						ADD `price8` VARCHAR( 10 ) NULL AFTER `price8_description`,
						ADD `price9_description` VARCHAR( 50 ) NULL AFTER `price8` ,
						ADD `price9` VARCHAR( 10 ) NULL AFTER `price9_description`,
						ADD `price10_description` VARCHAR( 50 ) NULL AFTER `price9` ,
						ADD `price10` VARCHAR( 10 ) NULL AFTER `price10_description`");

    	$this->execute("ALTER TABLE `menu_addons`
					  DROP `item_limit`,
					  DROP `quantity`,
					  DROP `enable_price_beyond`,
					  DROP `meal_part`");

    	$this->execute("ALTER TABLE `menu_addons` ADD `description` VARCHAR( 50 ) NULL AFTER `addon_option` ,
						ADD `price1_description` VARCHAR( 50 ) NULL AFTER `price` ,
						ADD `price2` VARCHAR( 10 ) NULL AFTER `price1_description`,
						ADD `price2_description` VARCHAR( 50 ) NULL AFTER `price2` ,
						ADD `price3` VARCHAR( 10 ) NULL AFTER `price2_description`,
						ADD `price3_description` VARCHAR( 50 ) NULL AFTER `price3` ,
						ADD `price4` VARCHAR( 10 ) NULL AFTER `price3_description`,
						ADD `price4_description` VARCHAR( 50 ) NULL AFTER `price4` ,
						ADD `price5` VARCHAR( 10 ) NULL AFTER `price4_description`,
						ADD `price5_description` VARCHAR( 50 ) NULL AFTER `price5` ,
						ADD `price6` VARCHAR( 10 ) NULL AFTER `price5_description`,
						ADD `price6_description` VARCHAR( 50 ) NULL AFTER `price6` ,
						ADD `price7` VARCHAR( 10 ) NULL AFTER `price6_description`,
						ADD `price7_description` VARCHAR( 50 ) NULL AFTER `price7` ,
						ADD `price8` VARCHAR( 10 ) NULL AFTER `price7_description`,
						ADD `price8_description` VARCHAR( 50 ) NULL AFTER `price8` ,
						ADD `price9` VARCHAR( 10 ) NULL AFTER `price8_description`,
						ADD `price9_description` VARCHAR( 50 ) NULL AFTER `price9` ,
						ADD `price10` VARCHAR( 10 ) NULL AFTER `price9_description`,
						ADD `price10_description` VARCHAR( 50 ) NULL AFTER `price10` ");

    	$this->execute("CREATE TABLE IF NOT EXISTS `menu_addon_settings` (
					  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
					  `menu_id` int(11) unsigned NOT NULL,
					  `addon_id` int(11) unsigned NOT NULL,
					  `item_limit` varchar(100) DEFAULT NULL,
					  `quantity_no` varchar(100) DEFAULT NULL,
					  `enable_pricing_beyond` varchar(100) DEFAULT NULL,
					  `meal_part` varchar(100) DEFAULT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB");*/
    }//up()

    public function down()
    {
    	//$this->drop_table("menu_addon_settings");
    }//down()
}
