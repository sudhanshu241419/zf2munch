<?php

class ChangeColumnWorseFreetowasFreeUserOrderAddons extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `user_order_addons` CHANGE `worse_free` `was_free` ENUM( '1', '0' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '0'");
    }//up()

    public function down()
    {
    }//down()
}
