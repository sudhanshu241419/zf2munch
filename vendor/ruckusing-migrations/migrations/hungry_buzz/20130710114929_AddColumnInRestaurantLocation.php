<?php

class AddColumnInRestaurantLocation extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute('ALTER TABLE `restaurants_location` ADD `neighborhood_latitude` DOUBLE NULL AFTER `longitude` ,
		ADD `neighborhood_longitude` DOUBLE NULL AFTER `neighborhood_latitude`');
    }//up()

    public function down()
    {
    	$this->remove_column("restaurants_location", "neighborhood_latitude");
    	$this->remove_column("restaurants_location", "neighborhood_longitude");
    }//down()
}
