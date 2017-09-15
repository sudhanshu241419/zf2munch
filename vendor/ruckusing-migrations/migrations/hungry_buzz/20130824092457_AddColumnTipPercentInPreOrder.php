<?php

class AddColumnTipPercentInPreOrder extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `pre_order` ADD `tip_percent` INT( 5 ) NULL DEFAULT NULL AFTER `tip` ");
    	$this->execute("ALTER TABLE `user_orders` ADD `tip_percent` INT( 5 ) NULL DEFAULT NULL AFTER `tip_amount` ");
    
    }//up()

    public function down()
    {
    	
    }//down()
}
