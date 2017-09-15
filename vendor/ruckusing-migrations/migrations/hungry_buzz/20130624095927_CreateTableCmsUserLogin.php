<?php

class CreateTableCmsUserLogin extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("CREATE TABLE `cms_users` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `username` varchar(20) NOT NULL,
		  `password` varchar(128) NOT NULL,
		  `email` varchar(128) NOT NULL,
		  `activkey` varchar(128) NOT NULL DEFAULT '',
		  `create_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
		  `lastvisit_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
		  `superuser` int(1) NOT NULL DEFAULT '0',
		  `status` int(1) NOT NULL DEFAULT '0',
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `username` (`username`),
		  UNIQUE KEY `email` (`email`),
		  KEY `status` (`status`),
		  KEY `superuser` (`superuser`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8;"); 

	    $this->execute("CREATE TABLE `cms_profiles` (
		  `user_id` int(11) NOT NULL AUTO_INCREMENT,
		  `lastname` varchar(50) NOT NULL DEFAULT '',
		  `firstname` varchar(50) NOT NULL DEFAULT '',
		  PRIMARY KEY (`user_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;");
	    
		    
	    $this->execute("CREATE TABLE `cms_profiles_fields` (
		  `id` int(10) NOT NULL AUTO_INCREMENT,
		  `varname` varchar(50) NOT NULL,
		  `title` varchar(255) NOT NULL,
		  `field_type` varchar(50) NOT NULL,
		  `field_size` varchar(15) NOT NULL DEFAULT '0',
		  `field_size_min` varchar(15) NOT NULL DEFAULT '0',
		  `required` int(1) NOT NULL DEFAULT '0',
		  `match` varchar(255) NOT NULL DEFAULT '',
		  `range` varchar(255) NOT NULL DEFAULT '',
		  `error_message` varchar(255) NOT NULL DEFAULT '',
		  `other_validator` varchar(5000) NOT NULL DEFAULT '',
		  `default` varchar(255) NOT NULL DEFAULT '',
		  `widget` varchar(255) NOT NULL DEFAULT '',
		  `widgetparams` varchar(5000) NOT NULL DEFAULT '',
		  `position` int(3) NOT NULL DEFAULT '0',
		  `visible` int(1) NOT NULL DEFAULT '0',
		  PRIMARY KEY (`id`),
		  KEY `varname` (`varname`,`widget`,`visible`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
		");
		$this->execute("INSERT INTO `cms_users` (`id`, `username`, `password`, `email`, `activkey`, `superuser`, `status`) VALUES
		(NULL, 'admin', '21232f297a57a5a743894a0e4a801fc3', 'webmaster@example.com', '9a24eff8c15a6a141ece27eb6947da0f', 1, 1),
		(NULL, 'demo', 'fe01ce2a7fbac8fafaed7c982a04e229', 'demo@example.com', '099f825543f7850cc038b90aaff39fac', 0, 1);");
	
		$this->execute("INSERT INTO `cms_profiles` (`user_id`, `lastname`, `firstname`) VALUES
		(NULL, 'Admin', 'Administrator'),
		(NULL, 'Demo', 'Demo');");

		$this->execute("INSERT INTO `cms_profiles_fields` (`id`, `varname`, `title`, `field_type`, `field_size`, `field_size_min`, `required`, `match`, `range`, `error_message`, `other_validator`, `default`, `widget`, `widgetparams`, `position`, `visible`) VALUES
		(NULL, 'lastname', 'Last Name', 'VARCHAR', 50, 3, 1, '', '', 'Incorrect Last Name (length between 3 and 50 characters).', '', '', '', '', 1, 3),
		(NULL, 'firstname', 'First Name', 'VARCHAR', 50, 3, 1, '', '', 'Incorrect First Name (length between 3 and 50 characters).', '', '', '', '', 0, 3);");
    }//up()

    public function down()
    {
    	$this->execute("cms_users");
    	$this->drop_table("cms_profiles");
    	$this->drop_table("cms_profiles_fields");
    }//down()
}
