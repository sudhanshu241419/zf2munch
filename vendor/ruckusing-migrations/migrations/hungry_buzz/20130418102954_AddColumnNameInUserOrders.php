<?php

class AddColumnNameInUserOrders extends Ruckusing_Migration_Base
{
    public function up()
    {
    		$this->add_column("user_orders", "delivery_time", "timestamp");
    } 

    public function down()
    {
    	$this->remove_column("user_orders", "delivery_time");
    }
}
