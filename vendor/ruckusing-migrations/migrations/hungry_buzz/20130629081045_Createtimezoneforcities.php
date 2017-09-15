<?php

class Createtimezoneforcities extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->add_column("cities", "time_zone", "string" );
    }//up()

    public function down()
    {
    	$this->remove_column("cities", "time_zone");

    }//down()
}
