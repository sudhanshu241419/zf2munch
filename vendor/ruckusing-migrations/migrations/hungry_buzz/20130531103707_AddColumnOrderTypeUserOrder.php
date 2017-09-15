<?php

class AddColumnOrderTypeUserOrder extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->add_column("user_orders", "order_type1", "string", array('limit' => 1,'comment' => 'I=>Individual,G=>Group'));
    	$this->add_column("pre_order", "zipcode", "integer", array('limit' => 11));
    }//up()

    public function down()
    {
    	$this->remove_column("user_orders", "order_type1", "string", array('limit' => 1,'comment' => 'I=>Individual,G=>Group'));
    	$this->remove_column("pre_order", "zipcode", "integer", array('limit' => 11));
    }//down()
}
