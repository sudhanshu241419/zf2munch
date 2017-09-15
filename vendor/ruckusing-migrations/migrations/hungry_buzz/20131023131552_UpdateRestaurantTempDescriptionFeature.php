<?php

class UpdateRestaurantTempDescriptionFeature extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `restaurant_temp` ADD `is_chain` VARCHAR( 10 ) NULL AFTER `zip`");
    	
    	$this->execute("ALTER TABLE `restaurant_temp` ADD `accept_card_on_phone` VARCHAR( 10 ) NOT NULL AFTER `accept_credit_cards`");
    	
    	$this->execute("ALTER TABLE `restaurant_temp` ADD `good_for_groups_desc` VARCHAR( 255 ) NULL AFTER `good_for_groups`");
    	
    	$this->execute("ALTER TABLE `restaurant_temp` ADD `family_friendly` VARCHAR( 100 ) NULL AFTER `good_for_kids`");
    	
    	$this->execute("ALTER TABLE `restaurant_temp` ADD `delivery_time_breakfast` VARCHAR( 255 ) NULL AFTER `delivery_instructions` ,
			ADD `delivery_time_lunch` VARCHAR( 255 ) NULL AFTER `delivery_time_breakfast` ,
			ADD `delivery_time_dinner` VARCHAR( 255 ) NULL AFTER `delivery_time_lunch`");
    	
    	$this->execute("ALTER TABLE `restaurant_temp` ADD `reservations` VARCHAR( 10 ) NOT NULL AFTER `prixfixe`");
    	
    	$this->execute("ALTER TABLE `restaurant_temp` ADD `fb_link` VARCHAR( 255 ) NULL ,
			ADD `twitter_link` VARCHAR( 255 ) NULL ,
			ADD `no_of_seats` VARCHAR( 255 ) NULL");
    	
    	$this->execute("ALTER TABLE `restaurants_details` ADD `accept_cc_phone` TINYINT( 1 ) UNSIGNED NULL DEFAULT '0',
			ADD `reservations` TINYINT( 1 ) UNSIGNED NULL DEFAULT '0',
			ADD `good_for_group_desc` VARCHAR( 255 ) NULL");
    	
    	$this->execute("INSERT INTO `hungry_buzz`.`features` (
			`id` ,`features` ,`feature_type` ,`feature_desc` ,`search_status` ,`status` ,`features_key`)
			VALUES (NULL , 'Family Friendly', 'Ambience', '', '1', '1', NULL)");   
    }//up()

    public function down()
    {
    }//down()
}
