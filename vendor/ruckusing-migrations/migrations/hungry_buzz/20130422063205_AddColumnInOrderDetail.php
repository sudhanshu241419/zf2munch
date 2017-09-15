<?php

class AddColumnInOrderDetail extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->add_column("user_order_details", "item_description", "text");
    } 

    public function down()
    {
    	$this->remove_column("user_order_details", "item_description");
    }
}
