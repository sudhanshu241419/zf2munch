<?php

class ChangeUserOrderCity extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `user_orders` CHANGE `city_code` `state_code` VARCHAR( 10 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL ");
    	$this->execute("ALTER TABLE `pre_order` CHANGE `city_code` `state_code` VARCHAR( 10 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL ");
    }//up()

    public function down()
    {
    }//down()
}
