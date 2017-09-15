<?php

class CreateTableRestaurantImageTemp extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("CREATE TABLE IF NOT EXISTS `restaurant_images_temp` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `rest_code` varchar(50) NOT NULL,
		  `image_name` varchar(255) NOT NULL,
		  `image_type` varchar(20) NOT NULL,
		  `image_path` varchar(255) NOT NULL,
		  `parent_folder` varchar(255) NOT NULL,
		  `image_without_ext` varchar(255) NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ");
    }//up()

    public function down()
    {
    	$this->execute("drop table restaurant_images_temp");
    }//down()
}
