<?php

class MaodificationInCusineTable extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("DELETE FROM `cuisines` WHERE `cuisines`.`id` IN (30,61,66,67,63,69,74,77,79,80,88)");
    	$this->execute("UPDATE `cuisines` SET `cuisine` = 'Sandwiches & Wraps' WHERE `cuisines`.`id` =70");
    	$this->execute("UPDATE `cuisines` SET `cuisine` = 'Vegan / Vegetarian' WHERE `cuisines`.`id` =76");
    	$this->execute("UPDATE `cuisines` SET `cuisine` = 'Smoothies / Juice Bar' WHERE `cuisines`.`id` =71");
    	$this->execute("UPDATE `cuisines` SET `cuisine` = 'Ice Cream / Frozen Yogurt' WHERE `cuisines`.`id` =78");
    	$this->execute("INSERT INTO `cuisines` (`id`, `cuisine`, `cuisine_type`, `description`, `image_name`, `created_on`, `search_status`, `status`) VALUES (NULL, 'Mediterranean', 'Europe', NULL, NULL, NOW(), '1', '1')");
    	$this->execute("INSERT INTO `cuisines` (`id`, `cuisine`, `cuisine_type`, `description`, `image_name`, `created_on`, `search_status`, `status`) VALUES (NULL, 'Irish', 'Europe', NULL, NULL, NOW(), '1', '1')");
    	$this->execute("INSERT INTO `cuisines` (`id`, `cuisine`, `cuisine_type`, `description`, `image_name`, `created_on`, `search_status`, `status`) VALUES (NULL, 'Cajun & Creole', 'North America', NULL, NULL, NOW(), '1', '1')");
    	$this->execute("INSERT INTO `cuisines` (`id`, `cuisine`, `cuisine_type`, `description`, `image_name`, `created_on`, `search_status`, `status`) VALUES (NULL, 'Bar Food', 'Fast Food/ Fav', NULL, NULL, NOW(), '1', '1')");
    	$this->execute("INSERT INTO `cuisines` (`id`, `cuisine`, `cuisine_type`, `description`, `image_name`, `created_on`, `search_status`, `status`) VALUES (NULL, 'Beverages', 'Fast Food/ Fav', NULL, NULL, NOW(), '1', '1')");
    	$this->execute("INSERT INTO `cuisines` (`id`, `cuisine`, `cuisine_type`, `description`, `image_name`, `created_on`, `search_status`, `status`) VALUES (NULL, 'Seafood', 'Fast Food/ Fav', NULL, NULL, NOW(), '1', '1')");
    	$this->execute("INSERT INTO `cuisines` (`id`, `cuisine`, `cuisine_type`, `description`, `image_name`, `created_on`, `search_status`, `status`) VALUES (NULL, 'BBQ', 'Fast Food/ Fav', NULL, NULL, NOW(), '1', '1')");
    	$this->execute("INSERT INTO `cuisines` (`id`, `cuisine`, `cuisine_type`, `description`, `image_name`, `created_on`, `search_status`, `status`) VALUES (NULL, 'Salad', 'Fast Food/ Fav', NULL, NULL, NOW(), '1', '1')");
    	$this->execute("INSERT INTO `cuisines` (`id`, `cuisine`, `cuisine_type`, `description`, `image_name`, `created_on`, `search_status`, `status`) VALUES (NULL, 'Kosher', 'Trends/Preferences', NULL, NULL, NOW(), '1', '1')");
    	$this->execute("INSERT INTO `cuisines` (`id`, `cuisine`, `cuisine_type`, `description`, `image_name`, `created_on`, `search_status`, `status`) VALUES (NULL, 'Halaal', 'Trends/Preferences', NULL, NULL, NOW(), '1', '1')");
    	$this->execute("ALTER TABLE `restaurants_details` CHANGE `meals` `meals` VARCHAR( 255 ) NULL DEFAULT NULL ");
    	$this->execute("UPDATE `cuisines` SET `cuisine_type` = 'Fast Food/ Fav' WHERE `cuisines`.`cuisine_type` ='Popular,Fast Food'");
    	$this->execute("UPDATE `cuisines` SET `cuisine_type` = 'Trends/Preferences' WHERE `cuisines`.`cuisine_type` ='Trends'");
    }//up()

    public function down()
    {
    }//down()
}
