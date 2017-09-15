<?php

class ChangeRangeDataType extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `pre_order` CHANGE `sub_total` `sub_total` FLOAT( 7, 2 ) NULL DEFAULT '0.00'");
    	$this->execute("ALTER TABLE `pre_order` CHANGE `tax` `tax` FLOAT( 7, 2 ) NULL DEFAULT '0.00'");
    	$this->execute("ALTER TABLE `pre_order` CHANGE `tip` `tip` FLOAT( 7, 2 ) NULL DEFAULT '0.00'");
    	$this->execute("ALTER TABLE `pre_order_item` CHANGE `total_item_amt` `total_item_amt` FLOAT( 7, 2 ) NULL DEFAULT '0.00'");
   		$this->execute("ALTER TABLE `pre_order_item` CHANGE `unit_price` `unit_price` FLOAT( 7, 2 ) NULL DEFAULT '0.00'");
    
		$this->execute("ALTER TABLE `user_orders` CHANGE `order_amount` `order_amount` DECIMAL( 7, 2 ) NULL DEFAULT '0.00'"); 
		$this->execute("ALTER TABLE `user_orders` CHANGE `tax` `tax` DECIMAL( 7, 2 ) NULL DEFAULT '0.00'"); 
   		$this->execute("ALTER TABLE `user_orders` CHANGE `tip_amount` `tip_amount` DECIMAL( 7, 2 ) NULL DEFAULT '0.00'");
   		$this->execute("ALTER TABLE `user_orders` CHANGE `total_amount` `total_amount` DECIMAL( 8, 2 ) NULL DEFAULT '0.00'");

   		$this->execute("ALTER TABLE `user_order_details` CHANGE `total_item_amt` `total_item_amt` DECIMAL( 8, 2 ) NULL DEFAULT '0.00'");
   		$this->execute("ALTER TABLE `user_order_details` CHANGE `unit_price` `unit_price` DECIMAL( 8, 2 ) NULL DEFAULT '0.00'");
    }//up()

    public function down()
    {
    }//down()
}
