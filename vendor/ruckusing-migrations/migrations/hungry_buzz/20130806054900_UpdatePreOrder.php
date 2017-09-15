<?php

class UpdatePreOrder extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `pre_order` CHANGE `order_status` `order_status` ENUM( '0', '1', '2' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL COMMENT '0=>pending,1=>checkout, 2=>cancel'");
    }//up()

    public function down()
    {
    }//down()
}
