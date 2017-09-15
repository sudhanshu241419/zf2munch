<?php

class AlterRestaurantRestaurantlogoname extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE  `restaurants` ADD  `restaurant_logo_name` VARCHAR( 256 ) NULL AFTER  `restaurant_video_name`");
    }//up()

    public function down()
    {
    }//down()
}
