<?php

class UseerMenuReviews extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("CREATE TABLE `user_menu_reviews` (
		  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		  `user_review_id` int(11) unsigned DEFAULT NULL,
		  `menu_id` int(11) DEFAULT NULL,
		  `image_name` varchar(255) DEFAULT NULL,
		  `liked` enum('0','1') NOT NULL COMMENT '0=>No,1=>Yes, null => did not click',
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1");
    }//up()

    public function down()
    {
    	$this->execute("drop table user_menu_reviews");
    }//down()
}
