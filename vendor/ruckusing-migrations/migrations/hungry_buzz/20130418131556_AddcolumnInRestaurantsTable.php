<?php

class AddcolumnInRestaurantsTable extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->add_column("restaurants", "restaurant_facilities", "integer");
    }//up()

    public function down()
    {
    	$this->remove_column("restaurants", "restaurant_facilities");
    }//down()
}
