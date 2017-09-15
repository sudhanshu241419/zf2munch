<?php

class RestaurantImages extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `restaurant_images` ADD `listOrder` INT( 11 ) NOT NULL DEFAULT '0' AFTER `is_read` ;"); 
        $this->execute("ALTER TABLE `restaurant_images` ADD `image_alt` VARCHAR( 255 ) NOT NULL AFTER `image_url` ;"); 
        $this->execute("ALTER TABLE `restaurant_images` ADD `image_link` TEXT NOT NULL AFTER `image_alt` ;"); 
    }//up()

    public function down()
    {
    }//down()
}
