<?php

class AlterRestaurantForVideo extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE  `restaurants` ADD  `restaurant_video_name` VARCHAR( 255 ) NULL AFTER  `restaurant_image_name`");
    }//up()

    public function down()
    {
    }//down()
}
