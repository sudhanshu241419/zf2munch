<?php

class CreateDealsCouponsTable extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("CREATE TABLE IF NOT EXISTS `restaurant_deals_coupons` (
		  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `restaurant_id` int(11) unsigned DEFAULT NULL,
		  `city_id` int(11) NOT NULL,
		  `type` enum('deals','coupons') DEFAULT 'deals',
		  `title` varchar(200) DEFAULT NULL,
		  `description` text,
		  `fine_print` text,
		  `price` decimal(5,2) DEFAULT NULL,
		  `discount_type` enum('f','p') DEFAULT NULL COMMENT 'f=>flat, p=>percentage',
		  `discount` tinyint(4) DEFAULT NULL,
		  `start_on` datetime DEFAULT NULL,
		  `end_date` datetime NOT NULL,
		  `expired_on` datetime DEFAULT NULL,
		  `created_on` datetime DEFAULT NULL,
		  `updated_at` datetime DEFAULT NULL,
		  `image` varchar(200) DEFAULT NULL,
		  `status` tinyint(4) DEFAULT 1 COMMENT '1=>live,0=>close,2=>paused,3=>processing',
		  `trend` tinyint(1) DEFAULT NULL,
		  `sold` int(11) DEFAULT '0',
		  `redeemed` int(11) DEFAULT '0',
		  PRIMARY KEY (`id`),
		  KEY `FK_restaurant_deals` (`restaurant_id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");
    }//up()

    public function down()
    {
    	$this->execute("DROP TABLE `restaurant_deals_coupons`");
    }//down()
}
