<?php

class AlterRestaurantEventForChangemenuid extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE  `restaurant_events` CHANGE  `menu_id`  `menu_id` VARCHAR( 255 ) NULL DEFAULT NULL");
    }//up()

    public function down()
    {
    }//down()
}
