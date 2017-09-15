<?php

class RestaurantStoryTemp extends Ruckusing_Migration_Base
{
     public function up()
    {
    	$this->execute("CREATE TABLE IF NOT EXISTS `restaurant_story_temp` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `Restaurant_Code` varchar(50) NOT NULL,
		  `chain_code` varchar(50) NOT NULL,
		  `restaurant_name` varchar(100) NOT NULL,
		  `restaurant_address` varchar(100) NOT NULL,
		  `title` varchar(1000) ,
		  `decor` varchar(1000) ,
		  `decor_image` varchar(200) ,
		  `atmosphere` varchar(1000) ,
		  `atmosphere_image` varchar(200) ,
		  `cuisine` varchar(200) ,
		  `cuisine_image` varchar(200) ,
		  `chef_story` text ,
		  `chef_story_image` varchar(200) ,
		  `neighborhood` varchar(1000) ,
		  `neighborhood_image` varchar(200) ,
		  `awards` text ,
		  `awards_image` varchar(200) ,
		  `service` text ,
		  `service_image` varchar(200) ,
		  `experience` text ,
		  `experience_image` varchar(200) ,
		  `ambience` varchar(2000) ,
		  `ambience_image` varchar(200) ,
		  `hover_links` varchar(1000) ,
		  `fun_facts` text ,
		  `fun_facts_image` varchar(200) ,
		  `location` varchar(1000) ,
		  `location_image` varchar(200) ,
		  `restaurant_history` varchar(2000) ,
		  `restaurant_history_image` varchar(200) ,
		  `other_info` varchar(1000) ,
		  `other_info_image` varchar(200) ,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;"); 

        $this->execute("CREATE TABLE IF NOT EXISTS `restaurant_story_temp_error` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `Restaurant_Code` varchar(50),
		  `chain_code` varchar(50),
		  `restaurant_name` varchar(100),
		  `restaurant_address` varchar(100) ,
		  `title` varchar(1000) ,
		  `decor` varchar(1000) ,
		  `decor_image` varchar(200) ,
		  `atmosphere` varchar(1000) ,
		  `atmosphere_image` varchar(200) ,
		  `cuisine` varchar(200) ,
		  `cuisine_image` varchar(200) ,
		  `chef_story` text ,
		  `chef_story_image` varchar(200) ,
		  `neighborhood` varchar(1000) ,
		  `neighborhood_image` varchar(200) ,
		  `awards` text ,
		  `awards_image` varchar(200) ,
		  `service` text ,
		  `service_image` varchar(200) ,
		  `experience` text ,
		  `experience_image` varchar(200) ,
		  `ambience` varchar(2000) ,
		  `ambience_image` varchar(200) ,
		  `hover_links` varchar(1000) ,
		  `fun_facts` text ,
		  `fun_facts_image` varchar(200) ,
		  `location` varchar(1000) ,
		  `location_image` varchar(200) ,
		  `restaurant_history` varchar(2000) ,
		  `restaurant_history_image` varchar(200) ,
		  `other_info` varchar(1000) ,
		  `other_info_image` varchar(200) ,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

       
        $this->execute('ALTER TABLE `restaurant_stories` 
        	            ADD `title` varchar(2000) ,
						ADD `decor` varchar(2000) ,
						ADD `decor_image` varchar(200) ,
						ADD `atmosphere` varchar(2000) ,
						ADD `atmosphere_image` varchar(200) ,
						ADD `cuisine` varchar(200) ,
						ADD `cuisine_image` varchar(200) ,
						ADD `chef_story_image` varchar(2000) ,
						ADD `neighborhood` varchar(2000) ,
						ADD `neighborhood_image` varchar(200) ,
						ADD `awards_image` varchar(200) ,
						ADD `service` text ,
						ADD `service_image` varchar(200) ,
						ADD `experience` text ,
						ADD `experience_image` varchar(200) ,
						ADD `ambience` varchar(2000) ,
						ADD `ambience_image` varchar(200) ,
						ADD `hover_links` varchar(2000) ,
					    ADD `fun_facts` text ,
					    ADD `fun_facts_image` varchar(200) ,
					    ADD `location` varchar(2000) ,
					    ADD `location_image` varchar(200) ,
					    ADD `restaurant_history_image` varchar(2000) ,
					    ADD `other_info_image` varchar(200)
        	          ');    
        
        $this->execute("ALTER TABLE `restaurant_images` 
        	           CHANGE `image_type` 
        	           `image_type` ENUM( 'd', 'c', 'g', 'e', 'b', 'ar', 'n', 'm', 'r', 's' ) 
        	            COMMENT 's=>stories'");
   
    }//up()

    public function down()
    {
    	$this->drop_table("restaurant_story_temp");
    	$this->drop_table("restaurant_story_temp_error");
    }//down()
}
