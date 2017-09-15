<?php

class CreateTableUserOrderAddons extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("CREATE TABLE IF NOT EXISTS `user_order_addons` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `user_order_details_id` int(11) NOT NULL,
		  `user_order_id` int(11) DEFAULT NULL,
		  `menu_addons_id` int(11) DEFAULT NULL,
		  `addons_name` varchar(100) DEFAULT NULL,
		  `addons_option` varchar(150) DEFAULT NULL,
		  `price` decimal(5,2) NOT NULL,
		  `quantity` int(11) NOT NULL DEFAULT '0',
		  `selection_type` tinyint(4) NOT NULL DEFAULT '0',
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=	1");
    }//up()

    public function down()
    {
    }//down()
}
