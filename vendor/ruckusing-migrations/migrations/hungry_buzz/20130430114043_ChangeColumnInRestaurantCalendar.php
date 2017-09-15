<?php

class ChangeColumnInRestaurantCalendar extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->change_column("restaurant_calendars", 'open_time', 'time');
    	$this->change_column("restaurant_calendars", 'close_time', 'time');
    }//up()

    public function down()
    {
    }//down()
}
