<?php

class AddColumnGmailPinterestInRestaurantsDetails extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `restaurants_details` ADD `gmail_url` VARCHAR( 255 ) NULL DEFAULT NULL AFTER `twitter_url`,
                        ADD `pinterest_url` VARCHAR( 255 ) NULL DEFAULT NULL AFTER `gmail_url`");
    }//up()

    public function down()
    {
    	$this->remove_column("restaurants_details","gmail_url");
    	$this->remove_column("restaurants_details","pinterest_url");
    }//down()
}
