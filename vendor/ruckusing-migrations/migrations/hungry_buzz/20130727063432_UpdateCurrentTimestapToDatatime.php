<?php

class UpdateCurrentTimestapToDatatime extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `menues` CHANGE `created_on` `created_on` DATETIME NULL");

    	$this->execute("ALTER TABLE `menu_bookmarks` CHANGE `created_on` `created_on` DATETIME NULL");

    	$this->execute("ALTER TABLE `point_source_detail` CHANGE `create_at` `created_at` DATETIME NULL");

    	$this->execute("ALTER TABLE `restaurants` CHANGE `updated_on` `updated_at` DATETIME NULL");

    	$this->execute("ALTER TABLE `restaurant_accounts` CHANGE `updated_on` `updated_at` DATETIME NULL");

    	$this->execute("ALTER TABLE `restaurant_blogs` CHANGE `date` `date` DATETIME NULL");

    	$this->execute("ALTER TABLE `restaurant_bookmarks` CHANGE `created_on` `created_on` DATETIME NULL");

    	$this->execute("ALTER TABLE `restaurant_coupons` CHANGE `updated_on` `updated_at` DATETIME NULL");

    	$this->execute("ALTER TABLE `restaurant_cuisines` CHANGE `created_on` `created_on` DATETIME NULL");


    	$this->execute("ALTER TABLE `restaurant_deals` CHANGE `updated_on` `updated_at` DATETIME NULL");


    	$this->execute("ALTER TABLE `restaurant_events` CHANGE `updated_on` `updated_at` DATETIME NULL");


    	$this->execute("ALTER TABLE `restaurant_fans` CHANGE `created_on` `created_on` DATETIME NULL");

    	$this->execute("ALTER TABLE `restaurant_images` CHANGE `updated_on` `updated_at` DATETIME NULL");

    	$this->execute("ALTER TABLE `restaurant_news` CHANGE `updated_on` `updated_at` DATETIME NULL");

	   	$this->execute("ALTER TABLE `restaurant_rattings` CHANGE `created_on` `created_on` DATETIME NULL");

    	$this->execute("ALTER TABLE `restaurant_videos` CHANGE `updated_on` `updated_at` DATETIME NULL");


    }//up()

    public function down()
    {
    }//down()
}
