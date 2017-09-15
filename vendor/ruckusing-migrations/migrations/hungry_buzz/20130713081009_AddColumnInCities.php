<?php

class AddColumnInCities extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute('ALTER TABLE `cities` ADD `latitude` DOUBLE NULL AFTER `state_code` ,
		ADD `longitude` DOUBLE NULL AFTER `latitude`');
    }//up()

    public function down()
    {
    	$this->remove_column("cities","latitude");
    	$this->remove_column("cities","longitude");
    }//down()
}
