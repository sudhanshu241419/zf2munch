<?php

class UpdateTableRestaurantCalendars extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `restaurant_calendars` ADD `takeout_open` TINYINT( 1 ) NOT NULL DEFAULT '1' COMMENT 'takeout_open 0 means restaurent will not take takeout order in this timeslot' AFTER `status`");
    }//up()

    public function down()
    {
    }//down()
}
