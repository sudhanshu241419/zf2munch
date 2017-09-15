<?php

class ChangeRestaurentDetailsColumn extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->remove_column("restaurants_details", "lunch");
    	$this->remove_column("restaurants_details", "breakfast");
    	$this->remove_column("restaurants_details", "dinner");
    }//up()

    public function down()
    {
    	$this->add_column("restaurants_details", "lunch", "boolean");
    	$this->add_column("restaurants_details", "breakfast", "boolean");
    	$this->add_column("restaurants_details", "dinner", "boolean");

    }//down()
}
