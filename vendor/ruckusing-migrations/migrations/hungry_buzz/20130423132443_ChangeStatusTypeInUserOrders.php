<?php

class ChangeStatusTypeInUserOrders extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `user_orders` CHANGE `status` `status` ENUM( 'ordered', 'confirmed', 'delivered', 'arrived', 'cancelled', 'frozen' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL");
    	$this->execute("ALTER TABLE `user_orders` ADD `frozen_status` ENUM( '1', '2', '3', '4' ) NOT NULL COMMENT '1=>ordered,2=>confirmed,3=>delivered,4=>arrived'");
    }//up()

    public function down()
    {
    	$this->execute("ALTER TABLE `user_orders` CHANGE `status` `status` ENUM( 'ordered', 'confirmed', 'delivered', 'arrived', 'cancelled' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL");    	
    	$this->remove_column("user_orders", "frozen_status");
    }//down()
}
