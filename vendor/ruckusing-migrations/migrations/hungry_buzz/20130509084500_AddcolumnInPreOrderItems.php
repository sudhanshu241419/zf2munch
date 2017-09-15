<?php

class AddcolumnInPreOrderItems extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->add_column("pre_order_item", "pre_order_addon_data", "text");
    }//up()

    public function down()
    {
    	$this->remove_column("pre_order_item", "pre_order_addon_data", "text");
    }//down()
}

