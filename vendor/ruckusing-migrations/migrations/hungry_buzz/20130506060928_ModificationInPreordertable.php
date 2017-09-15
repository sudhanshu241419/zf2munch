<?php

class ModificationInPreordertable extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$sql = "CREATE TABLE IF NOT EXISTS `pre_order` (
				  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
				  `restaurant_id` int(11) NOT NULL,
				  `user_id` int(11) DEFAULT NULL,
				  `user_sess_id` varchar(100) NOT NULL,
				  `city_code` varchar(255) DEFAULT NULL,
				  `city` varchar(255) NOT NULL,
				  `address` varchar(255) NOT NULL,
				  `lattitude` float NOT NULL,
				  `longitude` float NOT NULL,
				  `delivery_area` float DEFAULT NULL,
				  `order_type` varchar(255) NOT NULL,
				  `order_token` varchar(20) NOT NULL,
				  `order_status` enum('0','1') NOT NULL COMMENT '0=>pending,1=>checkout',
				  `delivery_time` datetime DEFAULT NULL,
				  `min_order_exceed` double(5,2) DEFAULT NULL,
				  `sub_total` double(5,2) DEFAULT NULL,
				  `delivery_charges` double(5,2) DEFAULT NULL,
				  `tax` double(5,2) DEFAULT NULL,
				  `tip` double(5,2) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
		$sql_1 = "CREATE TABLE IF NOT EXISTS `pre_order_item` (
					  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
					  `pre_order_id` int(11) NOT NULL,
					  `user_id` int(11) DEFAULT NULL,
					  `item` varchar(255) DEFAULT NULL,
					  `item_id` int(11) NOT NULL,
					  `quantity` int(11) DEFAULT NULL,
					  `unit_price` double(5,2) DEFAULT NULL,
					  `total_item_amt` double(5,2) DEFAULT NULL,
					  `item_description` varchar(255) DEFAULT NULL,
					  `special_instruction` varchar(255) DEFAULT NULL,
					  `order_token` varchar(20) NOT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
		$this->execute($sql);
		$this->execute($sql_1);
	}//up()

    public function down()
    {
    	
    }//down()
}
