<?php

class AddColumnNameActualAmountInUserOrders extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `user_orders` ADD `actual_amount` DECIMAL( 8, 2 ) NULL AFTER `agent`");
    }//up()

    public function down()
    {
    }//down()
}
