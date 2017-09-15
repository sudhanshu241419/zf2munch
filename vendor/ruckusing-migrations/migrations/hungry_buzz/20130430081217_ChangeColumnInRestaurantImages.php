<?php

class ChangeColumnInRestaurantImages extends Ruckusing_Migration_Base
{
    public function up()
    {	// modified the column to accept news type images
    	$this->execute("alter table restaurant_images change image_type image_type ENUM('d','c','g','e','b','ar','n')");
    }//up()

    public function down()
    {
    }//down()
}
