<?php

class CreatePromocodes extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("CREATE TABLE IF NOT EXISTS `promocodes` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `start_on` datetime NOT NULL,
		  `end_date` datetime NOT NULL,
		  `discount` int(11) NOT NULL,
		  `discount_type` varchar(25) NOT NULL,
		  `status` tinyint(4) NOT NULL COMMENT '0=>inactive,1=>active',
		  `minimum_order_amount` int(11) NOT NULL,
		  `slots` varchar(512) NOT NULL,
		  `days` varchar(45) NOT NULL,
		  `deal_for` varchar(25) NOT NULL,
		  `title` varchar(100) NOT NULL,
		  `description` varchar(255) DEFAULT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");
    }//up()

    public function down()
    {
    }//down()
}
