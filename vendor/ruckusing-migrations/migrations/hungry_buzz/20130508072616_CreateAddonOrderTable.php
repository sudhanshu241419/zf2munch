<?php

class CreateAddonOrderTable extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$sql = "CREATE TABLE IF NOT EXISTS `pre_order_addons` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `pre_order_item_id` int(11) NOT NULL,
      `pre_order_id` int(11) DEFAULT NULL,
      `menu_addons_id` int(11) DEFAULT NULL,
      `addons_name` varchar(100) DEFAULT NULL,
      `addons_option` varchar(150) DEFAULT NULL,
      `price` decimal(5,2) NOT NULL,
      	PRIMARY KEY (`id`)
    	) ENGINE=InnoDB DEFAULT CHARSET=latin1";
		$this->execute($sql);
    }//up()

    public function down()
    {
    }//down()
}
