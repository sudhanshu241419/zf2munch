<?php

class AddNewColumnTagsMaps extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("alter table tags_maps add column restaurant_id int(11)");
    	$this->execute("CREATE TABLE `restaurant_reviews_raw` (
						  `id` int(11) NOT NULL AUTO_INCREMENT,
						  `restaurant_code` varchar(50) NOT NULL,
						  `chain_code` varchar(50) DEFAULT NULL,
						  `restaurant_name` varchar(100) DEFAULT NULL,
						  `restaurant_address` varchar(255) DEFAULT NULL,
						  `url` varchar(150) DEFAULT NULL,
						  `source` varchar(50) DEFAULT NULL,
						  `date` varchar(30) DEFAULT NULL,
						  `reviewer` varchar(255) DEFAULT NULL,
						  `reviews` text NOT NULL,
						  `positive` varchar(150) DEFAULT NULL,
						  `consolidated_reviews` text,
						  `tags` varchar(255) DEFAULT NULL,
						  PRIMARY KEY (`id`),
						  UNIQUE KEY `IDX_for_unique` (`reviews`(50))
						) ENGINE=InnoDB  DEFAULT CHARSET=latin1");

    	$this->execute("CREATE TABLE `restaurant_blogs_raw` (
						  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
						  `restaurant_code` varchar(20) NOT NULL,
						  `chain_code` varchar(100) NOT NULL,
						  `restaurant_name` varchar(100) NOT NULL,
						  `restaurant_address` varchar(255) NOT NULL,
						  `source_url` varchar(150) NOT NULL,
						  `type` varchar(200) NOT NULL,
						  `blog_title` varchar(100) NOT NULL,
						  `date` varchar(25) NOT NULL,
						  `blogger` varchar(100) NOT NULL,
						  `post` text NOT NULL,
						  `image` varchar(5) NOT NULL,
						  `tag` varchar(255) NOT NULL,
						  PRIMARY KEY (`id`)
						) ENGINE=InnoDB  DEFAULT CHARSET=latin1");

    	$this->execute("ALTER TABLE `restaurant_blogs` ADD UNIQUE   `CHK_UNIQE` ( `post` ( 50 ) )");
    }//up()

    public function down()
    {
    	$this->execute("alter table tags_maps drop column restaurant_id");
    	$this->execute("drop table restaurant_reviews_raw");
    	$this->execute("drop table restaurant_blogs_raw");
    	$this->execute("alter table restaurant_blogs drop index CHK_UNIQE");
    }//down()
}
