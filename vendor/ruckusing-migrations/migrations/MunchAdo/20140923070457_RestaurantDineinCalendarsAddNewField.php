<?php

class RestaurantDineinCalendarsAddNewField extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute('ALTER TABLE `restaurant_Dinein_calendars` ADD `dinningtime_small` INT NOT NULL AFTER `dinner_seats` ,ADD `dinningtime_large` INT NOT NULL AFTER `dinningtime_small` ');
    }//up()

    public function down()
    {
    }//down()
}
