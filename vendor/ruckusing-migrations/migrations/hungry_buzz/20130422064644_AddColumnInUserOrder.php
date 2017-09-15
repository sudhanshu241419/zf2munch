<?php

class AddColumnInUserOrder extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->add_column("user_orders", "tax", "decimal", array('precision' => 5, 'scale' => 2));
    	$this->add_column("user_orders", "tip_amount", "decimal", array('precision' => 5, 'scale' => 2));
    	$this->add_column("user_orders", "delivery_charge", "decimal", array('precision' => 5, 'scale' => 2));
    	$this->add_column("user_orders", "delivery_address", "text");
    }//up()

    public function down()
    {
    	$this->remove_column("user_orders", "tax");
    	$this->remove_column("user_orders", "tip_amount");
    	$this->remove_column("user_orders", "delivery_charge");
    	$this->remove_column("user_orders", "delivery_address");
    }//down()
}
