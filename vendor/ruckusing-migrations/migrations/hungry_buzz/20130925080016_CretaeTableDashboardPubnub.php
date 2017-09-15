<?php

class CretaeTableDashboardPubnub extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("CREATE TABLE IF NOT EXISTS `pubnub_dashboard_notification` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `user_id` int(11) DEFAULT NULL,
					  `notification_msg` varchar(255) NOT NULL,
					  `type` tinyint(4) NOT NULL COMMENT '1=>order,2=>group order,3=>reservation,4=>reviews,5=>deals',
					  `restaurant_id` int(11) DEFAULT NULL,
					  `channel` varchar(100) DEFAULT NULL,
					  `created_on` datetime DEFAULT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;
					");
    }//up()

    public function down()
    {
    	$this->drop_table("pubnub_dashboard_notification");
    }//down()
}
