<?php

class AddColumnStatusPreOrderItem extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `pre_order_item` ADD `status` TINYINT( 1 ) NOT NULL DEFAULT '1'");
    }//up()

    public function down()
    {
    }//down()
}
