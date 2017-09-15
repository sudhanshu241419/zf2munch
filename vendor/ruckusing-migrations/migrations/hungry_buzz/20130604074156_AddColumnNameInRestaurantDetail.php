<?php

class AddColumnNameInRestaurantDetail extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->add_column("restaurants_details", "sentiments", "integer");
    }//up()

    public function down()
    {
    	//$this->remove_column("restaurants_details", "sentiments", "integer");
    }//down()
}
