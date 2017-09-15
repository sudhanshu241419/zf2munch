<?php

class AddColumnInRestaurantCuisines extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `restaurant_cuisines` ADD `status` ENUM( '0', '1' ) NULL DEFAULT '1' COMMENT '0=>inactive, 1=>active' AFTER `cuisine_id` ");
    }//up()

    public function down()
    {
    	$this->remove_column("restaurant_cuisines","status");
    }//down()
}
