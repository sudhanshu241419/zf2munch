<?php

class CreateMerchantRegistrationTable extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("CREATE TABLE IF NOT EXISTS `merchant_registration` (
		  `id` int(255) NOT NULL AUTO_INCREMENT,
		  `restaurant_name` varchar(200) DEFAULT NULL,
		  `street_address` varchar(500) NULL,
		  `apt_suite` varchar(500) NULL,
		  `city` varchar(50) NULL,
		  `state` varchar(50) NULL,
		  `zipcode` varchar(20) NULL,
		  `web_url` varchar(200) NULL,
		  `phone` varchar(100) NULL,
		  `email` varchar(200) NULL,
		  `operation_fname` varchar(100) NULL,
		  `operation_lname` varchar(100) NULL,
		  `operation_email` varchar(200) NULL,
		  `account_fname` varchar(100) NULL,
		  `account_lname` varchar(100) NULL,
		  `account_email` varchar(200) NULL,
		  `bank_fname` varchar(100) NULL,
		  `bank_lname` varchar(100) NULL,
		  `bank_email` varchar(200) NULL,
		  `created_on` datetime NULL,
		  `updated_on` datetime NULL,
		  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '0->unverified,1->verified',
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");
    }//up()

    public function down()
    {
    }//down()
}
