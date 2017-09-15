<?php

class AlterUserRestaurantImageAddCaption extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `user_restaurant_image` ADD `caption` VARCHAR( 255 ) NULL AFTER `image` ;");
    }//up()

    public function down()
    {
    }//down()
}
