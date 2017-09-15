<?php

class AddNullTypeUserOrder extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `user_orders` CHANGE `fname` `fname` VARCHAR( 100 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL ");
    	$this->execute("ALTER TABLE `user_orders` CHANGE `lname` `lname` VARCHAR( 100 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL ");
    	$this->execute("ALTER TABLE `user_orders` CHANGE `city_code` `city_code` VARCHAR( 10 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL ");
    	$this->execute("ALTER TABLE `user_orders` CHANGE `phone` `phone` VARCHAR( 20 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL ");
    	$this->execute("ALTER TABLE `user_orders` CHANGE `apt_suite` `apt_suite` VARCHAR( 100 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL ");
    	$this->execute("ALTER TABLE `user_orders` CHANGE `city` `city` VARCHAR( 50 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL ");
    	$this->execute("ALTER TABLE `user_orders` CHANGE `payment_status` `payment_status` VARCHAR( 50 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL ");
    	$this->execute("ALTER TABLE `user_orders` CHANGE `frozen_status` `frozen_status` ENUM( '1', '2', '3', '4' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT '1=>ordered,2=>confirmed,3=>delivered,4=>arrived'");
    }//up()

    public function down()
    {
    }//down()
}
