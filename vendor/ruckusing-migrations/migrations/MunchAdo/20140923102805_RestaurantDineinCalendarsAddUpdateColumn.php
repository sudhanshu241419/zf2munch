<?php

class RestaurantDineinCalendarsAddUpdateColumn extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute('ALTER TABLE `restaurant_Dinein_calendars` ADD `updatedAt` DATETIME NOT NULL AFTER `dinningtime_large` ');
    	
    }//up()

    public function down()
    {
    }//down()
}
