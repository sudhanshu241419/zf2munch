<?php

class CreateTableUserRestaurantImage extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("CREATE TABLE IF NOT EXISTS `user_restaurant_image` (
    	`id` int(11) NOT NULL AUTO_INCREMENT,
    	`user_id` int(11) NOT NULL,
    	`restaurant_id` int(11) NOT NULL,
    	`image` varchar(255) NOT NULL,
    	`image_url` varchar(255) NOT NULL,
    	`created_on` datetime NOT NULL,
    	`updated_on` datetime NOT NULL,
    	`status` enum('0','1') NOT NULL DEFAULT '0',
    	`image_type` enum('g','s') NOT NULL DEFAULT 'g' COMMENT 'g=gallery, s=story',
    	PRIMARY KEY (`id`)
    	) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='This table is used, when user upload photo of restaurant' AUTO_INCREMENT=1");
    	 
    }//up()

    public function down()
    {
    }//down()
}
