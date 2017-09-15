<?php

class CreateRestaurantNotificationSettings extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("CREATE TABLE IF NOT EXISTS `restaurant_notification_settings` (
					  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
					  `restaurant_id` int(11) DEFAULT NULL,
					  `new_order_received` tinyint(1) DEFAULT NULL,
					  `order_cancellation` tinyint(1) DEFAULT NULL,
					  `new_reservation_received` tinyint(1) DEFAULT NULL,
					  `reservation_cancellation` tinyint(1) DEFAULT NULL,
					  `new_deal_coupon_purchased` tinyint(1) DEFAULT NULL,
					  `new_review_posted` tinyint(1) DEFAULT NULL,
					  `important_system_updates` tinyint(1) DEFAULT NULL,
					  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
					 PRIMARY KEY (`id`)
					) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");
    	$this->execute("ALTER TABLE `restaurant_notification_settings` ADD UNIQUE (`restaurant_id`)");
    }//up()

    public function down()
    {
    	$this->drop_table('restaurant_notification_settings') ; 
    }//down()
}
