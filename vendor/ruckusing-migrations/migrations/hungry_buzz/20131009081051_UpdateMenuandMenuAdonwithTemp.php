<?php

class UpdateMenuandMenuAdonwithTemp extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `menues_temp` ADD `addons_number` VARCHAR( 50 ) NULL AFTER `tag_name` ,
                        ADD `spice_indicator` VARCHAR( 100 ) NULL AFTER `addons_number`");
        $this->execute("ALTER TABLE `menues_temp_error` ADD `addons_number` VARCHAR( 50 ) NULL AFTER `tag_name` ,
                        ADD `spice_indicator` VARCHAR( 100 ) NULL AFTER `addons_number` ");
        $this->execute("ALTER TABLE `menues` ADD `addons_number` VARCHAR( 50 ) NULL  ,
                        ADD `spice_indicator` VARCHAR( 100 ) NULL");
        $this->execute("ALTER TABLE `addons_temp` ADD `item_limit` VARCHAR( 100 ) NULL AFTER `price` ,
                        ADD `quantity` VARCHAR( 100 ) NULL AFTER `item_limit` ,
                        ADD `enable_price_beyond` VARCHAR( 100 ) NULL AFTER `quantity` ,
                        ADD `meal_part` VARCHAR( 100 ) NULL AFTER `enable_price_beyond` ,
                        ADD `description` VARCHAR( 255 ) NULL AFTER `menu_name`  ");
        $this->execute("ALTER TABLE `menu_addons` ADD `item_limit` VARCHAR( 100 ) NULL AFTER `price` ,
                        ADD `quantity` VARCHAR( 100 ) NULL AFTER `item_limit` ,
                        ADD `enable_price_beyond` VARCHAR( 100 ) NULL AFTER `quantity` ,
                        ADD `meal_part` VARCHAR( 100 ) NULL AFTER `enable_price_beyond`   ");
    }//up()

    public function down()
    {
    }//down()
}
