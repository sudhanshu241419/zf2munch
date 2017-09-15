<?php

class ChangeDataTypePreOrder extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `pre_order` CHANGE `sub_total` `sub_total` float( 5, 2 ) NULL DEFAULT '0.00'");
    	$this->execute("ALTER TABLE `pre_order` CHANGE `delivery_charges` `delivery_charges` float( 5, 2 ) NULL DEFAULT '0.00'");
    	$this->execute("ALTER TABLE `pre_order` CHANGE `tax` `tax` float( 5, 2 ) NULL DEFAULT '0.00'");
    	$this->execute("ALTER TABLE `pre_order` CHANGE `tip` `tip` float( 5, 2 ) NULL DEFAULT '0.00'");
    	$this->execute("ALTER TABLE `pre_order_item` CHANGE `unit_price` `unit_price` float( 5, 2 ) NULL DEFAULT '0.00'");
    	$this->execute("ALTER TABLE `pre_order_item` CHANGE `total_item_amt` `total_item_amt` float( 5, 2 ) NULL DEFAULT '0.00'");
    }//up()

    public function down()
    {
    }//down()
}
