<?php

class AlterUserRestaurantImageForSource extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `user_restaurant_image`  ADD `source` ENUM('0','1') NOT NULL DEFAULT '0' COMMENT '0=munchado.com,1=iphone' AFTER `image_url`");
    }//up()

    public function down()
    {
    }//down()
}
