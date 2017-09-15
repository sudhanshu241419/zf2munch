<?php

class CreateTableRestaurantDineinCalendars extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("CREATE TABLE IF NOT EXISTS `restaurant_Dinein_calendars` (
							  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
							  `restaurant_id` int(11) NOT NULL,
							  `calendar_day` enum('su','mo','tu','we','th','fr','sa') DEFAULT NULL,
							  `breakfast_start_time` time DEFAULT NULL,
							  `breakfast_end_time` time DEFAULT NULL,
							  `lunch_start_time` time DEFAULT NULL,
							  `lunch_end_time` time DEFAULT NULL,
							  `dinner_start_time` time DEFAULT NULL,
							  `dinner_end_time` time DEFAULT NULL,
							  `status` tinyint(4) NOT NULL DEFAULT '1',
							  PRIMARY KEY (`id`)
							) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");
    }//up()

    public function down()
    {
    	$this->drop_table('restaurant_Dinein_calendars') ; 
    }//down()
}
