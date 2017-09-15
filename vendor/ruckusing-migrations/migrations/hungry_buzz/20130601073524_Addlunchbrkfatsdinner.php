<?php

class Addlunchbrkfatsdinner extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->add_column("restaurants_details", "lunch", "boolean");
    	$this->add_column("restaurants_details", "breakfast", "boolean");
    	$this->add_column("restaurants_details", "dinner", "boolean");
    }//up()

    public function down()
    {
    	$this->remove_column("restaurants_details", "lunch");
    	$this->remove_column("restaurants_details", "brekfast");
    	$this->remove_column("restaurants_details", "dinner");
    }//down()

}
