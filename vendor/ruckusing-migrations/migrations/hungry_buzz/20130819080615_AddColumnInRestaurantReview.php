<?php

class AddColumnInRestaurantReview extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `restaurant_reviews` ADD `source_url` VARCHAR( 255 ) NULL"); 
    }//up()

    public function down()
    {
    	$this->remove_column("restaurant_reviews","source_url");
    }//down()
}
