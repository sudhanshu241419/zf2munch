<?php

class AlterRestaurantConfigNewfield extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE  `restaurant_config` ADD  `favicon_icon` VARCHAR( 255 ) NOT NULL AFTER  `meta_keyword` ,
ADD  `page_title` TEXT NOT NULL AFTER  `favicon_icon` ,
ADD  `og_title` VARCHAR( 255 ) NOT NULL AFTER  `page_title` ,
ADD  `og_url` VARCHAR( 255 ) NOT NULL AFTER  `og_title` ,
ADD  `og_image` VARCHAR( 255 ) NOT NULL AFTER  `og_url` ,
ADD  `og_desc` TEXT NOT NULL AFTER  `og_image` ,
ADD  `og_sitename` VARCHAR( 255 ) NOT NULL AFTER  `og_desc` ,
ADD  `fb_admins` VARCHAR( 255 ) NOT NULL AFTER  `og_sitename` ,
ADD  `twitter_title` TEXT NOT NULL AFTER  `fb_admins` ,
ADD  `twitter_desc` TEXT NOT NULL AFTER  `twitter_title` ,
ADD  `twitter_creator` VARCHAR( 255 ) NOT NULL AFTER  `twitter_desc`");
    }//up()

    public function down()
    {
    }//down()
}
