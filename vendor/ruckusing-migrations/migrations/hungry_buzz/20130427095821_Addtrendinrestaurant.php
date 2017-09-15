<?php

class Addtrendinrestaurant extends Ruckusing_Migration_Base
{
    public function up()
    {

    	$this->add_column("restaurants", "trend", "boolean", array("default" => false));

    }//up()

    public function down()
    {
    	$this->remove_column("restaurants", "trend", "boolean");
    }//down()
}
