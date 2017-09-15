<?php

class CreateCuisineMapTempTable extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("CREATE TABLE IF NOT EXISTS `cuisines_map_temp` ( `id` int(11) NOT NULL AUTO_INCREMENT,
		  `menu_id` int(11) NOT NULL,
		  `menu_name` varchar(255) NOT NULL,
		  `rest_name` varchar(255) NOT NULL,
		  `rest_id` int(11) NOT NULL,
		  `rest_code` varchar(255) NOT NULL,
		  `cuisine` varchar(255) NOT NULL,
		   PRIMARY KEY (`id`) 
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8;"); 

    	$this->execute("CREATE TABLE IF NOT EXISTS `cuisines_map_temp_error` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `menu_id` int(11),
		  `menu_name` varchar(255) ,
		  `rest_name` varchar(255) ,
		  `rest_id` int(11) ,
		  `rest_code` varchar(255) ,
		  `cuisine` varchar(255) ,
		   PRIMARY KEY (`id`) 
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8;"); 
    }//up()

    public function down()
    {
    	$this->drop_table("cuisines_map_temp");
    	$this->drop_table("cuisines_map_temp_error");
    }//down()
}
