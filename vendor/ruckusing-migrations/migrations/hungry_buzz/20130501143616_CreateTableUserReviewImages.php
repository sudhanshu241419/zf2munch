<?php

class CreateTableUserReviewImages extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$sql = "CREATE TABLE  `user_review_images` (
				  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				  `user_review_id` int(11) NOT NULL,
				  `order_item_id` int(11) NOT NULL,
				  `image_path` varchar(255) NOT NULL,
				  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=latin1";

		$this->execute($sql);
    }
    //up()

    public function down()
    {
    }//down()
}
