<?php

class ChangeDataTypeInOrderItem extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `pre_order_item` CHANGE `unit_price` `unit_price` DOUBLE( 5, 2 ) NULL DEFAULT '0.00'");
    	$this->execute("ALTER TABLE `pre_order_item` CHANGE `total_item_amt` `total_item_amt` DOUBLE( 5, 2 ) NULL DEFAULT '0.00'");
    }//up()

    public function down()
    {
    }//down()
}
