<?php

class AddColumnInUserOrders extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->add_column("user_orders", "payment_receipt", "string", array('limit' => 20));
    }//up()

    public function down()
    {
    	$this->remove_column("user_orders", "payment_receipt", "string", array('limit' => 20));
    }//down()
}
