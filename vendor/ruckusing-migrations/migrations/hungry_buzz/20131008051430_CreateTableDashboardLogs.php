<?php

class CreateTableDashboardLogs extends Ruckusing_Migration_Base
{
    public function up()
    {
     $this->execute("CREATE TABLE IF NOT EXISTS `dashboard_logs` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `user_id` int(11) DEFAULT NULL,
					  `restaurant_id` int(11) DEFAULT NULL,
					  `login_time` datetime DEFAULT NULL,
					  `activity` text NOT NULL,
					  `created_on` datetime DEFAULT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;");
	$this->execute("ALTER TABLE `user_orders` CHANGE `read` `is_read` TINYINT( 4 ) NOT NULL DEFAULT '0' COMMENT '0=>unread,1=>readed'");	
	$this->execute("ALTER TABLE `user_reservations` CHANGE `read` `is_read` TINYINT( 4 ) NOT NULL DEFAULT '0' COMMENT '0=>unread,1=>readed'");

    }//up()

    public function down()
    {
    }//down()
}
 