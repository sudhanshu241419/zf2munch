<?php

class AddColumnUserReview extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `user_reviews` ADD `replied` TINYINT( 1 ) NOT NULL DEFAULT '0',
			ADD `restaurant_response` TEXT NULL ");
    }//up()

    public function down()
    {
    	$this->remove_column("user_reviews","replied");
    	$this->remove_column("user_reviews","restaurant_response");
    }//down()
}
