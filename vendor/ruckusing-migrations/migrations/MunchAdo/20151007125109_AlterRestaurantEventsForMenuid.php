<?php

class AlterRestaurantEventsForMenuid extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE  `restaurant_events` ADD  `menu_id` VARCHAR( 255 ) NOT NULL AFTER  `restaurant_id`");
    }//up()

    public function down()
    {
    }//down()
}
