<?php

class CreateTableCronOrder extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("CREATE TABLE IF NOT EXISTS `cron_order` (
 						 `id` bigint(20) NOT NULL AUTO_INCREMENT,
						  `order_id` int(11) NOT NULL,
						  `delivery_time` datetime NOT NULL,
						  `arrived_time` datetime NOT NULL,
						  `status` tinyint(4) NOT NULL,
						  PRIMARY KEY (`id`)
						) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
					");
    }//up()

    public function down()
    {
    }//down()
}
