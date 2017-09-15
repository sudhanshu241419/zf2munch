<?php

class UpdateMenuAdonForMenuPrice extends Ruckusing_Migration_Base
{
    public function up()
    {
    	//$this->execute("ALTER TABLE `menu_addons` ADD `menu_price_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `price10_description`");

    	/*$this->execute("ALTER TABLE `menu_addons`  DROP `price2`,  DROP `price2_description`,
                        DROP `price3`, DROP `price3_description`, DROP `price4`, DROP `price4_description`,
                        DROP `price5`, DROP `price5_description`, DROP `price6`, DROP `price6_description`,
                        DROP `price7`, DROP `price7_description`, DROP `price8`, DROP `price8_description`,
                        DROP `price9`, DROP `price9_description`, DROP `price10`,DROP `price10_description`");*/

    	//$this->execute("ALTER TABLE `menu_addons` CHANGE `price1_description` `price_description` VARCHAR( 50 ) NULL DEFAULT NULL");
    }//up()

    public function down()
    {
    }//down()
}
