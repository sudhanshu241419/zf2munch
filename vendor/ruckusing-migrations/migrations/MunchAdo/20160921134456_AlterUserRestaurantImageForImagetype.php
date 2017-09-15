<?php

class AlterUserRestaurantImageForImagetype extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `user_restaurant_image` CHANGE `image_type` `image_type` ENUM('g','s','b') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'g' COMMENT 'g=gallery, s=story, b=bill'");
    }//up()

    public function down()
    {
    }//down()
}
