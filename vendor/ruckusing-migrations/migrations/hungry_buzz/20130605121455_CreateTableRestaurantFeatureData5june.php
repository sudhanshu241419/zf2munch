<?php

class CreateTableRestaurantFeatureData5june extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("DELETE FROM `cuisines` WHERE  `id` = 76");
    	$this->execute("UPDATE `cuisines` SET `cuisine` = 'Himalayan / Nepalese' WHERE  `id` = 10");
    	$this->execute("UPDATE `cuisines` SET `cuisine` = 'Vegan / Vegetarian' WHERE  `id` = 84");
        $this->execute("INSERT INTO `features`(`features`,`feature_type`,`features_key`,`feature_desc`,`status`) VALUES ('Brunch', 'Restaurant Features', '', '1', '1')");
 	
    	//$this->execute("ALTER TABLE `restaurants` CHANGE `restaurant_image_id` `restaurant_image_name` VARCHAR( 255 ) NULL DEFAULT NULL");
    }//up()

    public function down()
    {
    }//down()
}
