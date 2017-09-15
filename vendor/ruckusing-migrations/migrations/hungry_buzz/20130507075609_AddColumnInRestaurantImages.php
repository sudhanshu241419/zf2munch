<?php

class AddColumnInRestaurantImages extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->add_column("restaurant_images", "image_dimension", 'string', array("limit" => 20));
    }//up()

    public function down()
    {
    }//down()
}
