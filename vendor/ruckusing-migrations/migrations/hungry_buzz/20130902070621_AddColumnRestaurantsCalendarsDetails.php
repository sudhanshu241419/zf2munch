<?php

class AddColumnRestaurantsCalendarsDetails extends Ruckusing_Migration_Base
{
    public function up()
    {
    	/*$this->execute("ALTER TABLE `restaurant_calendars` ADD `breakfast_start_time` TIME NULL AFTER `close_time` ,
							ADD `breakfast_end_time` TIME NULL AFTER `breakfast_start_time` ,
							ADD `lunch_start_time` TIME NULL AFTER `breakfast_end_time` ,
							ADD `lunch_end_time` TIME NULL AFTER `lunch_start_time` ,
							ADD `dinner_start_time` TIME NULL AFTER `lunch_end_time` ,
							ADD `dinner_end_time` TIME NULL AFTER `dinner_start_time`");*/

    	/*$this->execute("ALTER TABLE `restaurants_details` ADD `small_turnaround_time` TIME NULL AFTER `delivery_time` ,
						ADD `large_turnaround_time` TIME NULL AFTER `small_turnaround_time` ,
						ADD `max_partysize` INT( 5 ) NULL AFTER `large_turnaround_time` ");*/
    }//up()

    public function down()
    {
    	$this->remove_column("restaurant_calendars","breakfast_start_time");
    	$this->remove_column("restaurant_calendars","breakfast_end_time");
    	$this->remove_column("restaurant_calendars","lunch_start_time");
    	$this->remove_column("restaurant_calendars","lunch_end_time");
    	$this->remove_column("restaurant_calendars","dinner_start_time");
    	$this->remove_column("restaurant_calendars","dinner_end_time");

    	$this->remove_column("restaurants_details","small_turnaround_time");
    	$this->remove_column("restaurants_details","large_turnaround_time");
    	$this->remove_column("restaurants_details","max_partysize");
    }//down()
}
